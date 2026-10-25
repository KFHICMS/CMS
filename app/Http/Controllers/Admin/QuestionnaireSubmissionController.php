<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireResponseEdit;
use App\Services\QuestionnaireAnswerRules;
use Illuminate\Support\Facades\DB;

class QuestionnaireSubmissionController extends Controller
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly QuestionnaireAnswerRules $answerRules
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'event_id' => ['nullable', 'integer'],
            'questionnaire_id' => ['nullable', 'integer'],
            'source' => ['nullable', 'in:invitation,logged_in'],
            'review_status' => ['nullable', 'in:pending,approved,rejected'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', Rule::when($request->filled('from'), ['after_or_equal:from'])],
        ]);

        $submissions = QuestionnaireResponse::query()
            ->with([
                'questionnaire:id,title,event_id',
                'questionnaire.event:id,title',
                'child:id,child_code',
                'submitter:id,name,role',
            ])
            ->when(
                $filters['event_id'] ?? null,
                fn ($query, $eventId) => $query->whereHas(
                    'questionnaire',
                    fn ($q) => $q->where('event_id', $eventId)
                )
            )
            ->when(
                $filters['questionnaire_id'] ?? null,
                fn ($query, $id) => $query->where('questionnaire_id', $id)
            )
            ->when(
                $filters['source'] ?? null,
                fn ($query, $source) => $source === 'invitation'
                    ? $query->whereNotNull('invitation_id')
                    : $query->whereNull('invitation_id')
            )
            ->when(
                $filters['review_status'] ?? null,
                fn ($query, $status) => $query->where('review_status', $status)
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $date) => $query->whereDate('submitted_at', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $date) => $query->whereDate('submitted_at', '<=', $date)
            )
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.questionnaire_submissions.index', [
            'submissions' => $submissions,
            'pending' => QuestionnaireResponse::query()->where('review_status', 'pending')->count(),
            'events' => Event::query()->orderByDesc('event_date')->limit(200)->get(['id', 'title', 'event_date']),
            'questionnaires' => Questionnaire::query()->orderBy('title')->limit(200)->get(['id', 'title']),
        ]);
    }

    public function show(QuestionnaireResponse $submission): View
    {
        $submission->load([
            'questionnaire.event:id,title,event_date',
            'child:id,child_code,first_name,middle_name,last_name',
            'submitter:id,name,role',
            'answers.question',
            'edits.editor:id,name',
        ]);

        $answers = $submission->answers
            ->sortBy(fn ($answer) => $answer->question?->sort_order ?? 0)
            ->values();

        $reviewerName = $submission->reviewed_by
            ? User::query()->whereKey($submission->reviewed_by)->value('name')
            : null;

        return view('admin.questionnaire_submissions.show', compact('submission', 'answers', 'reviewerName'));
    }

    public function review(Request $request, QuestionnaireResponse $submission): RedirectResponse
    {
        $validated = $request->validate([
            'review_status' => ['required', 'in:pending,approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $pending = $validated['review_status'] === 'pending';

        $submission->forceFill([
            'review_status' => $validated['review_status'],
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_by' => $pending ? null : $request->user()->id,
            'reviewed_at' => $pending ? null : now(),
        ])->save();

        // The note and answers are not copied into the audit log.
        $this->audit->record(
            $request,
            'questionnaire_reviewed',
            'questionnaire_response',
            $submission->id,
            'Questionnaire submission review status updated.'
        );

        return redirect()
            ->route('admin_submissions.show', $submission)
            ->with('success', 'Review saved.');
    }

        public function edit(QuestionnaireResponse $submission): View|RedirectResponse
    {
        if ($locked = $this->lockedResponse($submission)) {
            return $locked;
        }

        $submission->load(['questionnaire.event:id,title', 'child:id,child_code', 'answers']);

        $questions = $submission->questionnaire
            ->questions()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Pre-fill the shared question partial with the saved answers, unless a
        // failed save has just flashed what the admin typed.
        if (! session()->hasOldInput()) {
            $values = [];

            foreach ($submission->answers as $answer) {
                $values['question_'.$answer->question_id] = $this->answerValue($answer);
            }

            session()->now('_old_input', $values);
        }

        return view('admin.questionnaire_submissions.edit', compact('submission', 'questions'));
    }

    public function update(Request $request, QuestionnaireResponse $submission): RedirectResponse
    {
        if ($locked = $this->lockedResponse($submission)) {
            return $locked;
        }

        $questionnaire = $submission->questionnaire()->with('questions.options')->firstOrFail();

        $validated = $request->validate(
            $this->answerRules->rules($questionnaire) + [
                'edit_reason' => ['required', 'string', 'min:5', 'max:1000'],
            ],
            [],
            $this->answerRules->attributes($questionnaire) + ['edit_reason' => 'reason for the change']
        );

        $outcome = DB::transaction(function () use ($request, $submission, $questionnaire, $validated) {
            // Lock the row so a simultaneous approval or edit cannot interleave.
            $locked = QuestionnaireResponse::query()
                ->with('answers')
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if ($locked->review_status === 'approved') {
                return 'locked';
            }

            $existing = $locked->answers->keyBy('question_id');
            $changes = [];

            foreach ($questionnaire->questions as $question) {
                $new = $validated['question_'.$question->id] ?? null;
                $new = is_array($new) ? array_values($new) : $new;

                $answer = $existing->get($question->id);
                $old = $answer ? $this->answerValue($answer) : null;

                if ($this->sameAnswer($old, $new)) {
                    continue;
                }

                $changes[] = [
                    'question_id' => $question->id,
                    'question' => $question->question_text,
                    'old' => $old,
                    'new' => $new,
                ];

                $payload = [
                    'answer_text' => is_array($new) ? null : $new,
                    'answer_json' => is_array($new) ? $new : null,
                ];

                if ($answer) {
                    $answer->update($payload);
                } else {
                    $locked->answers()->create(['question_id' => $question->id] + $payload);
                }
            }

            if ($changes === []) {
                return 'unchanged';
            }

            QuestionnaireResponseEdit::create([
                'response_id' => $locked->id,
                'edited_by' => $request->user()->id,
                'reason' => $validated['edit_reason'],
                'changes' => $changes,
            ]);

            return 'saved';
        });

        if ($outcome === 'locked') {
            return redirect()
                ->route('admin_submissions.show', $submission)
                ->with('error', 'This submission was approved in the meantime and is now locked. Set it back to Pending to edit.');
        }

        if ($outcome === 'unchanged') {
            return back()->withInput()->withErrors(['edit_reason' => 'No answers were changed.']);
        }

        // Answer text is kept in the edit history, not in the audit log.
        $this->audit->record(
            $request,
            'questionnaire_answers_edited',
            'questionnaire_response',
            $submission->id,
            'Questionnaire submission answers edited by an administrator.'
        );

        return redirect()
            ->route('admin_submissions.show', $submission)
            ->with('success', 'Answers updated. The change is recorded in the edit history.');
    }

    private function lockedResponse(QuestionnaireResponse $submission): ?RedirectResponse
    {
        if ($submission->review_status !== 'approved') {
            return null;
        }

        return redirect()
            ->route('admin_submissions.show', $submission)
            ->with('error', 'Approved submissions are locked. Set the review status back to Pending to edit the answers.');
    }

    /** @return array|string|null */
    private function answerValue(QuestionnaireAnswer $answer): mixed
    {
        $json = $answer->answer_json;

        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        return is_array($json) ? $json : $answer->answer_text;
    }

    private function sameAnswer(mixed $old, mixed $new): bool
    {
        if (is_array($old) || is_array($new)) {
            $a = collect((array) $old)->map(fn ($value) => (string) $value)->sort()->values()->all();
            $b = collect((array) $new)->map(fn ($value) => (string) $value)->sort()->values()->all();

            return $a === $b;
        }

        return trim((string) $old) === trim((string) $new);
    }

    /** Streams the photo from private storage. Admin-only; the file has no public address. */
    public function photo(QuestionnaireResponse $submission): StreamedResponse
    {
        abort_unless($submission->photo_path && $submission->photo_disk, 404);

        $disk = Storage::disk($submission->photo_disk);
        abort_unless($disk->exists($submission->photo_path), 404);

        return $disk->response($submission->photo_path, null, [
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}