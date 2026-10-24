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

class QuestionnaireSubmissionController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
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