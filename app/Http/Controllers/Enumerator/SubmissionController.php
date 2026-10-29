<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'tab' => ['nullable', 'in:questionnaires,attendance'],
            'event_id' => ['nullable', 'integer'],
            'questionnaire_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:present,absent'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', Rule::when($request->filled('from'), ['after_or_equal:from'])],
        ]);

        $tab = $filters['tab'] ?? 'questionnaires';

        $attendedEventIds = Attendance::query()
            ->where('marked_by', $user->id)
            ->distinct()
            ->pluck('event_id');

        $answeredQuestionnaires = Questionnaire::query()
            ->whereIn(
                'id',
                QuestionnaireResponse::query()
                    ->where('submitted_by', $user->id)
                    ->select('questionnaire_id')
            )
            ->orderBy('title')
            ->get(['id', 'title', 'event_id']);

        // Events the enumerator is assigned to, plus any they have submitted for
        // (so history stays filterable after an assignment ends).
        $eventIds = $user->assignedEvents()->pluck('events.id')
            ->merge($attendedEventIds)
            ->merge($answeredQuestionnaires->pluck('event_id'))
            ->unique()
            ->values();

        $events = Event::query()
            ->whereIn('id', $eventIds)
            ->orderByDesc('event_date')
            ->get(['id', 'title', 'event_date']);

        // Counting rules: rows this enumerator created. Attendance is unique per
        // child and event, and a response is unique per child and questionnaire,
        // so repeated requests are never double-counted.
        $stats = [
            'attendance' => Attendance::query()->where('marked_by', $user->id)->count(),
            'present' => Attendance::query()->where('marked_by', $user->id)->where('status', 'present')->count(),
            'absent' => Attendance::query()->where('marked_by', $user->id)->where('status', 'absent')->count(),
            'questionnaires' => QuestionnaireResponse::query()->where('submitted_by', $user->id)->count(),
            'events' => $attendedEventIds->merge($answeredQuestionnaires->pluck('event_id'))->unique()->count(),
        ];

        $records = ($tab === 'attendance'
            ? $this->attendanceQuery($user->id, $filters)
            : $this->questionnaireQuery($user->id, $filters))
            ->paginate(15)
            ->withQueryString();

        return view('enumerator.submissions.index', [
            'tab' => $tab,
            'records' => $records,
            'events' => $events,
            'questionnaires' => $answeredQuestionnaires,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, QuestionnaireResponse $submission): View
    {
        abort_unless($submission->submitted_by === $request->user()->id, 404);

        $submission->load([
            'questionnaire.event:id,title,event_date',
            'child:id,child_code,first_name,middle_name,last_name',
            'answers.question',
        ]);

        $answers = $submission->answers
            ->sortBy(fn ($answer) => $answer->question?->sort_order ?? 0)
            ->values();

        return view('enumerator.submissions.show', compact('submission', 'answers'));
    }

    private function questionnaireQuery(int $userId, array $filters): Builder
    {
        return QuestionnaireResponse::query()
            ->where('submitted_by', $userId)
            ->with(['questionnaire:id,title,event_id', 'questionnaire.event:id,title', 'child:id,child_code'])
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
                $filters['from'] ?? null,
                fn ($query, $date) => $query->whereDate('submitted_at', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $date) => $query->whereDate('submitted_at', '<=', $date)
            )
            ->latest('submitted_at');
    }

    private function attendanceQuery(int $userId, array $filters): Builder
    {
        return Attendance::query()
            ->where('marked_by', $userId)
            ->with(['event:id,title', 'child:id,child_code'])
            ->when(
                $filters['event_id'] ?? null,
                fn ($query, $eventId) => $query->where('event_id', $eventId)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $date) => $query->whereDate('attendance_time', '>=', $date)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $date) => $query->whereDate('attendance_time', '<=', $date)
            )
            ->latest('attendance_time');
    }
}