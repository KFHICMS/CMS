<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Guardian;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the comprehensive report management page.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'demographics');

        // Demographics Aggregations
        $totalChildren = Child::count();
        $activeChildren = Child::where('status', 'active')->count();
        $inactiveChildren = Child::where('status', 'inactive')->count();

        $genderDistribution = Child::selectRaw('COALESCE(gender, "Unspecified") as gender, count(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        $districtDistribution = Child::selectRaw('COALESCE(district, "Unassigned") as district, count(*) as count')
            ->groupBy('district')
            ->orderByDesc('count')
            ->take(10)
            ->pluck('count', 'district');

        $gnDivisionDistribution = Child::selectRaw('COALESCE(gn_division, "Unassigned") as gn_division, count(*) as count')
            ->groupBy('gn_division')
            ->orderByDesc('count')
            ->take(10)
            ->pluck('count', 'gn_division');

        $gradeDistribution = Child::selectRaw('COALESCE(grade, "Unknown") as grade, count(*) as count')
            ->groupBy('grade')
            ->orderBy('grade')
            ->pluck('count', 'grade');

        // Family / Guardians
        $totalGuardians = Guardian::count();
        $guardiansWithPhone = Guardian::whereNotNull('phone')->where('phone', '!=', '')->count();

        // Events & Attendance Metrics
        $totalEvents = Event::count();
        $totalAttendanceMarks = Attendance::count();

        $eventsSummary = Event::withCount('attendances')
            ->orderBy('event_date', 'desc')
            ->take(15)
            ->get();

        $attendanceByMethod = Attendance::selectRaw('COALESCE(method, "manual") as method, count(*) as count')
            ->groupBy('method')
            ->pluck('count', 'method');

        // Questionnaire / Survey Metrics
        $totalQuestionnaires = Questionnaire::count();
        $totalResponses = QuestionnaireResponse::count();
        $questionnairesWithResponseCount = Questionnaire::with('event')
            ->withCount('responses')
            ->orderByDesc('responses_count')
            ->take(10)
            ->get();

        // Follow-ups Metrics
        $totalFollowUps = FollowUp::count();
        $followUpsByStatus = FollowUp::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $followUpsByPriority = FollowUp::selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $followUpsByCategory = FollowUp::selectRaw('COALESCE(category, "General") as category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $officerCaseload = User::whereHas('followUps')
            ->withCount([
                'followUps',
                'followUps as pending_follow_ups_count' => function ($q) {
                    $q->where('status', 'pending');
                },
                'followUps as completed_follow_ups_count' => function ($q) {
                    $q->where('status', 'completed');
                }
            ])
            ->get();

        // Filterable Children List for Report Export / Preview
        $childrenQuery = Child::with(['guardians']);

        if ($request->filled('district')) {
            $childrenQuery->where('district', $request->district);
        }
        if ($request->filled('gn_division')) {
            $childrenQuery->where('gn_division', 'like', '%' . $request->gn_division . '%');
        }
        if ($request->filled('gender')) {
            $childrenQuery->where('gender', $request->gender);
        }
        if ($request->filled('status')) {
            $childrenQuery->where('status', $request->status);
        }
        if ($request->filled('grade')) {
            $childrenQuery->where('grade', $request->grade);
        }

        $filteredChildren = $childrenQuery->orderBy('created_at', 'desc')->paginate(15);
        $availableDistricts = Child::whereNotNull('district')->distinct()->pluck('district');

        return view('admin.reports.index', compact(
            'activeTab',
            'totalChildren',
            'activeChildren',
            'inactiveChildren',
            'genderDistribution',
            'districtDistribution',
            'gnDivisionDistribution',
            'gradeDistribution',
            'totalGuardians',
            'guardiansWithPhone',
            'totalEvents',
            'totalAttendanceMarks',
            'eventsSummary',
            'attendanceByMethod',
            'totalQuestionnaires',
            'totalResponses',
            'questionnairesWithResponseCount',
            'totalFollowUps',
            'followUpsByStatus',
            'followUpsByPriority',
            'followUpsByCategory',
            'officerCaseload',
            'filteredChildren',
            'availableDistricts'
        ));
    }

    /**
     * Export Children Demographics to CSV.
     */
    public function exportChildrenCsv(Request $request): StreamedResponse
    {
        $fileName = 'children_report_' . date('Y-m-d_His') . '.csv';

        $query = Child::with(['guardians']);

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for UTF-8 Excel support

            // CSV Header
            fputcsv($file, [
                'Child Code',
                'First Name',
                'Last Name',
                'Gender',
                'DOB',
                'District',
                'GN Division',
                'School Name',
                'Grade',
                'Status',
                'Primary Guardian',
                'Guardian Phone',
                'Registered Date',
            ]);

            $query->chunk(200, function ($children) use ($file) {
                foreach ($children as $child) {
                    $primaryGuardian = $child->guardians->firstWhere('is_primary', true) ?? $child->guardians->first();

                    fputcsv($file, [
                        $child->child_code,
                        $child->first_name,
                        $child->last_name,
                        ucfirst($child->gender ?? 'N/A'),
                        $child->date_of_birth ? $child->date_of_birth->format('Y-m-d') : '',
                        $child->district,
                        $child->gn_division,
                        $child->school_name,
                        $child->grade,
                        ucfirst($child->status),
                        $primaryGuardian ? $primaryGuardian->full_name : 'No guardian recorded',
                        $primaryGuardian ? $primaryGuardian->phone : 'N/A',
                        $child->created_at ? $child->created_at->format('Y-m-d') : '',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Event Attendance to CSV.
     */
    public function exportAttendanceCsv(Request $request): StreamedResponse
    {
        $fileName = 'attendance_report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Attendance ID',
                'Event Title',
                'Event Date',
                'Child Code',
                'Child Name',
                'District',
                'Method',
                'Attendance Time',
                'Marked By',
            ]);

            Attendance::with(['event', 'child', 'markedBy'])
                ->chunk(200, function ($attendances) use ($file) {
                    foreach ($attendances as $att) {
                        fputcsv($file, [
                            $att->id,
                            $att->event ? $att->event->title : 'Unknown Event',
                            $att->event && $att->event->event_date ? $att->event->event_date->format('Y-m-d') : 'N/A',
                            $att->child ? $att->child->child_code : 'N/A',
                            $att->child ? trim($att->child->first_name . ' ' . $att->child->last_name) : 'N/A',
                            $att->child ? $att->child->district : 'N/A',
                            strtoupper($att->method ?? 'MANUAL'),
                            $att->attendance_time ? $att->attendance_time->format('Y-m-d H:i') : '',
                            $att->markedBy ? $att->markedBy->name : 'System',
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Follow-ups to CSV.
     */
    public function exportFollowUpsCsv(): StreamedResponse
    {
        $fileName = 'follow_ups_report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'Child Code',
                'Child Name',
                'Category',
                'Priority',
                'Status',
                'Assigned Officer',
                'Follow-up Date',
                'Next Date',
                'Description',
                'Action Taken',
            ]);

            FollowUp::with(['child', 'assignedTo'])
                ->chunk(200, function ($followUps) use ($file) {
                    foreach ($followUps as $item) {
                        fputcsv($file, [
                            $item->id,
                            $item->child ? $item->child->child_code : 'N/A',
                            $item->child ? trim($item->child->first_name . ' ' . $item->child->last_name) : 'N/A',
                            $item->category ?? 'General',
                            strtoupper($item->priority),
                            strtoupper($item->status),
                            $item->assignedTo ? $item->assignedTo->name : 'Unassigned',
                            $item->follow_up_date ? $item->follow_up_date->format('Y-m-d') : '',
                            $item->next_follow_up_date ? $item->next_follow_up_date->format('Y-m-d') : '',
                            $item->description,
                            $item->action_taken,
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
=======
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}
