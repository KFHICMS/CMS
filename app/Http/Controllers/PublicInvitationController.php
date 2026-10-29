<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\EnumeratorInvitation;
use App\Models\QuestionnaireResponse;
use App\Services\AuditLogService;
use App\Services\QrIdentificationService;
use App\Services\QuestionnaireAnswerRules;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Services\SubmissionPhotoStore;
use InvalidArgumentException;
use RuntimeException;

class PublicInvitationController extends Controller
{
    private const PROOF_MINUTES = 30;

        public function __construct(
        private readonly AuditLogService $audit,
        private readonly QuestionnaireAnswerRules $answerRules,
        private readonly QrIdentificationService $identifier,
        private readonly SubmissionPhotoStore $photos
    ) {
    }

    public function show(string $token): View|Response
    {
        $invitation = $this->find($token);

        if ($problem = $this->unavailable($invitation)) {
            return $this->unavailableResponse($problem);
        }

        $questionnaire = $invitation->questionnaire;
        $questions = $questionnaire->questions()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('public.invitations.form', [
            'event' => $invitation->event,
            'questionnaire' => $questionnaire,
            'questions' => $questions,
            'token' => $token,
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    public function thanks(string $token): View
    {
        $invitation = $this->find($token);
        $event = $invitation->event;
        $questionnaire = $invitation->questionnaire;

        $canSubmitAgain = $invitation->isUsable()
            && $questionnaire->status === 'published'
            && $event->enable_questionnaires
            && $event->collectionState() === 'open'
            && $this->submissionCount($invitation) < $invitation->max_submissions;

        return view('public.invitations.thanks', [
            'event' => $event,
            'questionnaire' => $questionnaire,
            'token' => $token,
            'canSubmitAgain' => $canSubmitAgain,
        ]);
    }

    /**
     * Identify an existing child from a scanned QR code. Only the child code is
     * returned; typed codes are not accepted so child codes cannot be probed.
     */
    public function identify(Request $request, string $token): JsonResponse
    {
        $invitation = $this->find($token);

        if ($problem = $this->unavailable($invitation)) {
            return response()->json(['success' => false, 'message' => $problem[1]], $problem[0]);
        }

        $validated = $request->validate(['qr' => ['required', 'string', 'max:2048']]);

        $result = $this->identifier->resolve($validated['qr'], null);

        if (! $result['child']) {
            // The scanned value is deliberately not logged.
            $this->audit->record(
                $request,
                'identify_failed',
                'public_questionnaire',
                $invitation->id,
                'Child identification failed: '.$result['reason'].'.'
            );

            return response()->json(['success' => false, 'message' => $result['message']], $result['status']);
        }

        $child = $result['child'];

        if ($this->alreadyAnswered($invitation, $child->id)) {
            return response()->json([
                'success' => false,
                'message' => 'This child has already answered this questionnaire.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'child' => ['child_code' => $child->child_code],
            'proof' => $this->makeProof($invitation, $child),
        ]);
    }

        public function store(Request $request, string $token): RedirectResponse|Response
    {
        $invitation = $this->find($token);

        // Re-checked on every submission, not only when the form was opened.
        if ($problem = $this->unavailable($invitation)) {
            return $this->unavailableResponse($problem);
        }

        $questionnaire = $invitation->questionnaire->loadMissing('questions.options');

        $validated = $request->validate(
            $this->answerRules->rules($questionnaire) + [
                'respondent_name' => ['required', 'string', 'min:2', 'max:100'],
                'submission_key' => ['required', 'uuid'],
                'child_mode' => ['required', 'in:existing,new'],
                'child_proof' => ['nullable', 'string', 'max:2000'],
                'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ],
            [
                'photo.required' => "Please add the child's photograph.",
                'photo.uploaded' => 'The photo could not be uploaded. Please choose a smaller photo and try again.',
                'photo.image' => 'The photo must be a JPG, PNG or WebP image.',
                'photo.mimes' => 'The photo must be a JPG, PNG or WebP image.',
                'photo.max' => 'The photo must be smaller than 5 MB.',
            ],
            $this->answerRules->attributes($questionnaire) + [
                'respondent_name' => 'your name',
                'child_mode' => 'child selection',
            ]
        );

        // An accidental retry (double tap, reload) must not create a second record or file.
        if (QuestionnaireResponse::query()->where('idempotency_key', $validated['submission_key'])->exists()) {
            return redirect()->route('public.invitations.thanks', $token);
        }

        $childId = null;

        if ($validated['child_mode'] === 'existing') {
            $childId = $this->childFromProof($validated['child_proof'] ?? null, $invitation);

            if (! $childId) {
                throw ValidationException::withMessages([
                    'child_proof' => "Scan the child's QR code again before submitting.",
                ]);
            }

            if ($this->alreadyAnswered($invitation, $childId)) {
                throw ValidationException::withMessages([
                    'child_proof' => 'This child has already answered this questionnaire.',
                ]);
            }
        }

        // Everything is valid: now store the photo on the private disk.
        try {
            $stored = $this->photos->store($request->file('photo'), $invitation->event_id);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['photo' => $exception->getMessage()]);
        } catch (RuntimeException) {
            throw ValidationException::withMessages(['photo' => 'The photo could not be saved. Please try again.']);
        }

        try {
            $outcome = DB::transaction(function () use ($invitation, $questionnaire, $validated, $childId, $stored) {
                // Lock this invitation so simultaneous submissions cannot exceed its cap.
                $locked = EnumeratorInvitation::query()->lockForUpdate()->findOrFail($invitation->id);

                if (! $locked->isUsable()) {
                    return ['problem' => [410, 'This link is no longer active. Please ask your coordinator for a new link.']];
                }

                if ($this->submissionCount($locked) >= $locked->max_submissions) {
                    return ['problem' => [409, 'This link has reached its submission limit. Please ask your coordinator.']];
                }

                $response = QuestionnaireResponse::create([
                    'questionnaire_id' => $questionnaire->id,
                    'child_id' => $childId,
                    'submitted_by' => null,
                    'submitted_at' => now(),
                    'response_uuid' => (string) Str::uuid(),
                    'invitation_id' => $locked->id,
                    'respondent_name' => $validated['respondent_name'],
                    'idempotency_key' => $validated['submission_key'],
                    'review_status' => 'pending',
                    'photo_path' => $stored['path'],
                    'photo_disk' => $stored['disk'],
                ]);

                foreach ($questionnaire->questions as $question) {
                    $answer = $validated['question_' . $question->id] ?? null;

                    $response->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => is_array($answer) ? null : $answer,
                        'answer_json' => is_array($answer) ? array_values($answer) : null,
                    ]);
                }

                return ['response' => $response];
            });
        } catch (UniqueConstraintViolationException) {
            // The save was rolled back, so remove the photo stored for it.
            $this->photos->delete($stored['disk'], $stored['path']);

            // Either the same retry raced (already saved), or this child was just answered.
            if (QuestionnaireResponse::query()->where('idempotency_key', $validated['submission_key'])->exists()) {
                return redirect()->route('public.invitations.thanks', $token);
            }

            return back()->withInput()->withErrors([
                'child_proof' => 'This child has already answered this questionnaire.',
            ]);
        } catch (\Throwable $exception) {
            $this->photos->delete($stored['disk'], $stored['path']);

            throw $exception;
        }

        if (isset($outcome['problem'])) {
            $this->photos->delete($stored['disk'], $stored['path']);

            return $this->unavailableResponse($outcome['problem']);
        }

        // No token, name, child, photo path or answers in the audit entry.
        $this->audit->record(
            $request,
            'public_submission_received',
            'public_questionnaire',
            $outcome['response']->id,
            'Public questionnaire response received through an invitation link.'
        );

        return redirect()->route('public.invitations.thanks', $token);
    }

    private function makeProof(EnumeratorInvitation $invitation, Child $child): string
    {
        return Crypt::encryptString(json_encode([
            'i' => $invitation->id,
            'c' => $child->id,
            'e' => now()->addMinutes(self::PROOF_MINUTES)->timestamp,
        ]));
    }

    /** Returns the identified child's ID, or null if the proof is missing, forged, expired or for another link. */
    private function childFromProof(?string $proof, EnumeratorInvitation $invitation): ?int
    {
        if (! $proof) {
            return null;
        }

        try {
            $data = json_decode(Crypt::decryptString($proof), true);
        } catch (DecryptException) {
            return null;
        }

        if (
            ! is_array($data)
            || ($data['i'] ?? null) !== $invitation->id
            || ($data['e'] ?? 0) < now()->timestamp
        ) {
            return null;
        }

        $childId = Child::query()
            ->whereKey((int) ($data['c'] ?? 0))
            ->where('status', 'active')
            ->value('id');

        return $childId ? (int) $childId : null;
    }

    private function alreadyAnswered(EnumeratorInvitation $invitation, int $childId): bool
    {
        return QuestionnaireResponse::query()
            ->where('questionnaire_id', $invitation->questionnaire_id)
            ->where('child_id', $childId)
            ->exists();
    }

    private function find(string $token): EnumeratorInvitation
    {
        $invitation = EnumeratorInvitation::query()
            ->where('token_hash', EnumeratorInvitation::hashToken($token))
            ->with(['event', 'questionnaire'])
            ->first();

        abort_unless(
            $invitation
                && $invitation->event
                && $invitation->questionnaire
                && $invitation->questionnaire->event_id === $invitation->event_id,
            404
        );

        return $invitation;
    }

    /** @return array{0:int,1:string}|null */
    private function unavailable(EnumeratorInvitation $invitation): ?array
    {
        $status = $invitation->status();

        if ($status === 'revoked') {
            return [410, 'This link is no longer active. Please ask your coordinator for a new link.'];
        }

        if ($status === 'expired') {
            return [410, 'This link has expired. Please ask your coordinator for a new link.'];
        }

        $event = $invitation->event;
        $questionnaire = $invitation->questionnaire;

        if ($questionnaire->status !== 'published') {
            return [403, 'This questionnaire is not available right now.'];
        }

        if (! $event->enable_questionnaires) {
            return [403, 'Questionnaires are not enabled for this event.'];
        }

        $state = $event->collectionState();

        if ($state !== 'open') {
            return [409, match ($state) {
                'cancelled' => 'This event has been cancelled.',
                'completed' => 'This event is completed and no longer accepts responses.',
                default => 'This event has not started yet. It opens on '.$event->event_date->format('M j, Y').'.',
            }];
        }

        if ($this->submissionCount($invitation) >= $invitation->max_submissions) {
            return [409, 'This link has reached its submission limit. Please ask your coordinator.'];
        }

        return null;
    }

    private function submissionCount(EnumeratorInvitation $invitation): int
    {
        return QuestionnaireResponse::query()->where('invitation_id', $invitation->id)->count();
    }

    /** @param array{0:int,1:string} $problem */
    private function unavailableResponse(array $problem): Response
    {
        return response()->view('public.invitations.unavailable', ['message' => $problem[1]], $problem[0]);
    }
}