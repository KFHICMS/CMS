<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnumeratorInvitation;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class EnumeratorInvitationController extends Controller
{
    private const DAY_CHOICES = [1, 3, 7, 14, 30];

    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function store(Request $request, Event $event, Questionnaire $questionnaire): RedirectResponse
    {
        $this->assertBelongs($event, $questionnaire);
        $validated = $this->validated($request);

        if ($questionnaire->status !== 'published') {
            return $this->back($event, $questionnaire)
                ->with('error', 'Publish the questionnaire before generating an enumerator link.');
        }

        [$invitation, $token] = $this->issue($request, $event, $questionnaire, $validated);

        $this->audit->record(
            $request,
            'invitation_created',
            'enumerator_invitation',
            $invitation->id,
            'Enumerator invitation link created.'
        );

        return $this->back($event, $questionnaire)
            ->with('new_invitation_url', url('/join/'.$token))
            ->with('success', 'Enumerator link generated. You can copy it again later from the table.');
    }

    public function revoke(
        Request $request,
        Event $event,
        Questionnaire $questionnaire,
        EnumeratorInvitation $invitation
    ): RedirectResponse {
        $this->assertOwns($event, $questionnaire, $invitation);

        $changed = EnumeratorInvitation::query()
            ->whereKey($invitation->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_by' => $request->user()->id,
            ]);

        if ($changed) {
            $this->audit->record(
                $request,
                'invitation_revoked',
                'enumerator_invitation',
                $invitation->id,
                'Enumerator invitation link revoked.'
            );
        }

        return $this->back($event, $questionnaire)
            ->with('success', 'Enumerator link revoked. It no longer opens the questionnaire.');
    }

    public function replace(
        Request $request,
        Event $event,
        Questionnaire $questionnaire,
        EnumeratorInvitation $invitation
    ): RedirectResponse {
        $this->assertOwns($event, $questionnaire, $invitation);
        $validated = $this->validated($request);

        if ($invitation->revoked_at !== null) {
            return $this->back($event, $questionnaire)
                ->with('error', 'That link was already revoked. Generate a new link instead.');
        }

        if ($questionnaire->status !== 'published') {
            return $this->back($event, $questionnaire)
                ->with('error', 'Publish the questionnaire before generating an enumerator link.');
        }

        [$new, $token] = DB::transaction(function () use ($request, $event, $questionnaire, $invitation, $validated) {
            $invitation->update([
                'revoked_at' => now(),
                'revoked_by' => $request->user()->id,
            ]);

            return $this->issue($request, $event, $questionnaire, $validated);
        });

        $this->audit->record(
            $request,
            'invitation_replaced',
            'enumerator_invitation',
            $new->id,
            'Enumerator invitation link replaced.'
        );

        return $this->back($event, $questionnaire)
            ->with('new_invitation_url', url('/join/'.$token))
            ->with('success', 'The old link was revoked and a new link was generated. You can copy it from the table.');
    }

    /** @return array{0: EnumeratorInvitation, 1: string} */
    private function issue(Request $request, Event $event, Questionnaire $questionnaire, array $validated): array
    {
        $token = EnumeratorInvitation::generateToken();

        $invitation = EnumeratorInvitation::create([
            'event_id' => $event->id,
            'questionnaire_id' => $questionnaire->id,
            'token_hash' => EnumeratorInvitation::hashToken($token),
            'token_hint' => substr($token, -4),
            'max_submissions' => $validated['max_submissions'],
            'expires_at' => now()->addDays((int) $validated['expires_in_days']),
            'created_by' => $request->user()->id,
            'token_encrypted' => Crypt::encryptString($token),
        ]);

        return [$invitation, $token];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'expires_in_days' => ['required', 'integer', Rule::in(self::DAY_CHOICES)],
            'max_submissions' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);
    }

    private function assertBelongs(Event $event, Questionnaire $questionnaire): void
    {
        abort_unless($questionnaire->event_id === $event->id, 404);
    }

    private function assertOwns(Event $event, Questionnaire $questionnaire, EnumeratorInvitation $invitation): void
    {
        $this->assertBelongs($event, $questionnaire);
        abort_unless(
            $invitation->questionnaire_id === $questionnaire->id && $invitation->event_id === $event->id,
            404
        );
    }

    private function back(Event $event, Questionnaire $questionnaire): RedirectResponse
    {
        return redirect()->route('events.questionnaires.show', [$event, $questionnaire]);
    }
}