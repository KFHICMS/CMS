<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\Program;
use App\Models\QuestionnaireResponse;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function index(Request $request): View
    {
        $f = $this->validated($request);
        $events = $this->eventsQuery($f);
        $eventIds = (clone $events)->select('events.id');

        $totals = [
            'children_registered' => $this->childrenQuery($f)->count(),
            'children_active' => Child::query()->where('status', 'active')->count(),
            'events' => (clone $events)->count(),
            'present' => Attendance::query()->whereIn('event_id', $eventIds)->where('status', 'present')->count(),
            'absent' => Attendance::query()->whereIn('event_id', $eventIds)->where('status', 'absent')->count(),
            'responses' => QuestionnaireResponse::query()
                ->whereHas('questionnaire', fn ($q) => $q->whereIn('event_id', $eventIds))
                ->count(),
            'field_submitted' => FieldActivitySubmission::query()
                ->whereIn('event_id', $eventIds)->where('status', 'submitted')->count(),
        ];

        $eventRows = (clone $events)
            ->with('program:id,name')
            ->withCount([
                'attendances as present_count' => fn ($q) => $q->where('status', 'present'),
                'attendances as absent_count' => fn ($q) => $q->where('status', 'absent'),
                'fieldActivitySubmissions as submitted_count' => fn ($q) => $q->where('status', 'submitted'),
            ])
            ->orderByDesc('event_date')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'filters' => $f,
            'programs' => Program::query()->orderBy('name')->get(['id', 'name']),
            'totals' => $totals,
            'participation' => $this->participationRows($f, $events),
            'registrations' => $this->registrationsByMonth($f),
            'eventRows' => $eventRows,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $f = $this->validated($request);
        $request->validate(['report' => ['required', 'in:participation,events,registrations']]);
        $report = $request->input('report');

        $this->audit->record(
            $request,
            'report_exported',
            'admin_report',
            null,
            "Admin {$report} report exported as CSV."
        );

        $filename = "kfhi-{$report}-".now()->format('Ymd-His').'.csv';
        $events = $this->eventsQuery($f);

        return response()->streamDownload(function () use ($report, $f, $events): void {
            $out = fopen('php://output', 'w');

            if ($report === 'participation') {
                fputcsv($out, ['Program', 'Status', 'Events', 'Present', 'Absent', 'Field submissions submitted'], ',', '"', '');
                foreach ($this->participationRows($f, $events) as $r) {
                    fputcsv($out, [$this->csvSafe($r['name']), $r['status'], $r['events'], $r['present'], $r['absent'], $r['submitted']], ',', '"', '');
                }
            } elseif ($report === 'registrations') {
                fputcsv($out, ['Month', 'Children registered'], ',', '"', '');
                foreach ($this->registrationsByMonth($f, null) as $r) {
                    fputcsv($out, [$r->month, $r->total], ',', '"', '');
                }
            } else {
                fputcsv($out, ['Event code', 'Event', 'Date', 'Program', 'Status', 'Present', 'Absent', 'Field submissions submitted'], ',', '"', '');
                (clone $events)->with('program:id,name')
                    ->withCount([
                        'attendances as present_count' => fn ($q) => $q->where('status', 'present'),
                        'attendances as absent_count' => fn ($q) => $q->where('status', 'absent'),
                        'fieldActivitySubmissions as submitted_count' => fn ($q) => $q->where('status', 'submitted'),
                    ])
                    ->chunkById(200, function ($chunk) use ($out): void {
                        foreach ($chunk as $e) {
                            fputcsv($out, [
                                $this->csvSafe($e->event_code), $this->csvSafe($e->title),
                                $e->event_date?->format('Y-m-d'), $this->csvSafe($e->program?->name),
                                $e->status, $e->present_count, $e->absent_count, $e->submitted_count,
                            ], ',', '"', '');
                        }
                    });
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
        ]);
    }

    private function eventsQuery(array $f): Builder
    {
        return Event::query()
            ->when($f['from'] ?? null, fn ($q, $d) => $q->whereDate('event_date', '>=', $d))
            ->when($f['to'] ?? null, fn ($q, $d) => $q->whereDate('event_date', '<=', $d))
            ->when($f['program_id'] ?? null, fn ($q, $id) => $q->where('program_id', $id));
    }

    private function childrenQuery(array $f): Builder
    {
        return Child::query()
            ->when($f['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($f['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
    }

    private function registrationsByMonth(array $f, ?int $limit = 24): Collection
    {
        $month = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $query = $this->childrenQuery($f)
            ->selectRaw("{$month} as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderByDesc('month');

        return $limit ? $query->limit($limit)->get() : $query->get();
    }

    private function participationRows(array $f, Builder $events): Collection
    {
        $eventIds = (clone $events)->select('events.id');

        $programs = Program::query()
            ->when($f['program_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $eventCounts = (clone $events)->selectRaw('program_id, COUNT(*) as c')
            ->groupBy('program_id')->pluck('c', 'program_id');

        $attendance = Attendance::query()
            ->join('events', 'events.id', '=', 'attendances.event_id')
            ->whereIn('attendances.event_id', $eventIds)
            ->selectRaw('events.program_id as pid, attendances.status as st, COUNT(*) as c')
            ->groupBy('events.program_id', 'attendances.status')
            ->get();

        $submitted = FieldActivitySubmission::query()
            ->join('events', 'events.id', '=', 'field_activity_submissions.event_id')
            ->whereIn('field_activity_submissions.event_id', $eventIds)
            ->where('field_activity_submissions.status', 'submitted')
            ->selectRaw('events.program_id as pid, COUNT(*) as c')
            ->groupBy('events.program_id')
            ->pluck('c', 'pid');

        return $programs->map(function (Program $p) use ($eventCounts, $attendance, $submitted) {
            $present = (int) $attendance->where('pid', $p->id)->where('st', 'present')->sum('c');
            $absent = (int) $attendance->where('pid', $p->id)->where('st', 'absent')->sum('c');

            return [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'events' => (int) ($eventCounts[$p->id] ?? 0),
                'present' => $present,
                'absent' => $absent,
                'rate' => ($present + $absent) > 0 ? round($present / ($present + $absent) * 100) : null,
                'submitted' => (int) ($submitted[$p->id] ?? 0),
            ];
        });
    }

    /** Stop spreadsheet apps from running cell values that start with = + - @ as formulas. */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}