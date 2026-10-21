<?php

namespace App\Http\Controllers\FieldOfficer;

use App\Http\Controllers\Controller;
use App\Models\FieldActivityBenefit;
use App\Models\FieldActivityObservation;
use App\Models\FieldActivitySubmission;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function index(Request $request): View
    {
        $officer = $request->user();
        $validated = $this->validated($request);
        $programs = $officer->programs()->orderBy('name')->get(['programs.id', 'name']);

        $base = $this->filteredQuery($officer, $validated);
        $submissionIds = (clone $base)->select('field_activity_submissions.id');

        $totals = [
            'records' => (clone $base)->count(),
            'submitted' => (clone $base)->where('status', 'submitted')->count(),
            'drafts' => (clone $base)->where('status', 'draft')->count(),
            'present' => (clone $base)->where('attendance_status', 'present')->count(),
            'absent' => (clone $base)->where('attendance_status', 'absent')->count(),
            'benefit_items' => (int) FieldActivityBenefit::query()
                ->whereIn('submission_id', $submissionIds)
                ->sum('quantity'),
            'observations' => FieldActivityObservation::query()
                ->whereIn('submission_id', $submissionIds)
                ->count(),
        ];

        $submissions = (clone $base)
            ->with([
                'event.program:id,name',
                'child:id,child_code,first_name,middle_name,last_name',
                'benefits',
                'observations',
            ])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $events = $officer->programs()
            ->with(['events' => fn ($query) => $query
                ->orderBy('event_date')
                ->orderBy('title')])
            ->get()
            ->flatMap(fn ($program) => $program->events)
            ->unique('id')
            ->sortBy('title')
            ->values();

        return view('field_officer.reports.index', compact(
            'programs',
            'events',
            'submissions',
            'validated',
            'totals'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $officer = $request->user();
        $validated = $this->validated($request);

        $query = $this->filteredQuery($officer, $validated)->with([
            'event.program:id,name',
            'child:id,child_code',
            'benefits:id,submission_id,quantity',
            'observations:id,submission_id',
        ]);

        $this->audit->record(
            $request,
            'report_exported',
            'field_activity_report',
            null,
            'Field activity report exported as CSV.'
        );

        $filename = 'field-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Activity', 'Activity date', 'Program', 'Child code', 'Attendance',
                'Benefit items', 'Field notes', 'Status', 'Submitted at',
            ], ',', '"', '');

            $query->chunkById(200, function ($chunk) use ($out): void {
                foreach ($chunk as $submission) {
                    fputcsv($out, [
                        $this->csvSafe($submission->event->title),
                        $submission->event->event_date?->format('Y-m-d'),
                        $this->csvSafe($submission->event->program?->name),
                        $this->csvSafe($submission->child->child_code),
                        $submission->attendance_status ?? '',
                        (int) $submission->benefits->sum('quantity'),
                        $submission->observations->count(),
                        $submission->status,
                        $submission->submitted_at?->format('Y-m-d H:i'),
                    ], ',', '"', '');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'status' => ['nullable', 'in:draft,submitted'],
        ]);
    }

    private function filteredQuery(User $officer, array $validated): Builder
    {
        return FieldActivitySubmission::query()
            ->where('user_id', $officer->id)
            ->whereHas(
                'event.program.officers',
                fn ($query) => $query->where('users.id', $officer->id)
            )
            ->when(
                $validated['from'] ?? null,
                fn ($query, $date) => $query->whereHas(
                    'event',
                    fn ($events) => $events->whereDate('event_date', '>=', $date)
                )
            )
            ->when(
                $validated['to'] ?? null,
                fn ($query, $date) => $query->whereHas(
                    'event',
                    fn ($events) => $events->whereDate('event_date', '<=', $date)
                )
            )
            ->when(
                $validated['program_id'] ?? null,
                fn ($query, $programId) => $query->whereHas(
                    'event',
                    fn ($events) => $events->where('program_id', $programId)
                )
            )
            ->when(
                $validated['event_id'] ?? null,
                fn ($query, $eventId) => $query->where('event_id', $eventId)
            )
            ->when(
                $validated['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            );
    }

    /** Stop spreadsheet apps from running cell values that start with = + - @ as formulas. */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}