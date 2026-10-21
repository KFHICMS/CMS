<?php

namespace App\Http\Controllers\FieldOfficer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\FollowUp;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $officer = $request->user();
        $programIds = $officer->programs()->pluck('programs.id');

        $programs = $officer->programs()
            ->withCount('events')
            ->with(['events' => fn ($query) => $query
            ->orderBy('event_date')
            ->orderBy('start_time')])
            ->orderBy('name')
            ->get();

        $todayActivities = Event::query()
            ->whereIn('program_id', $programIds)
            ->whereDate('event_date', today())
            ->with('program:id,name')
            ->orderBy('start_time')
            ->get();

        $followUps = FollowUp::query()
            ->where('assigned_to', $officer->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['child:id,child_code,first_name,middle_name,last_name', 'event:id,title'])
            ->orderBy('next_follow_up_date')
            ->orderBy('follow_up_date')
            ->paginate(8, ['*'], 'followups')
            ->withQueryString();

        $pendingSubmissions = FieldActivitySubmission::query()
            ->where('user_id', $officer->id)
            ->where('status', 'draft')
            ->whereHas(
                'event.program.officers',
                fn ($query) => $query->where('users.id', $officer->id)
            )
            ->with([
                'event:id,program_id,title,event_date',
                'event.program:id,name',
                'child:id,child_code',
            ])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('field_officer.dashboard', compact(
            'officer',
            'programs',
            'todayActivities',
            'followUps',
            'pendingSubmissions'
        ));
    }

        public function programs(Request $request): View
    {
        $programs = $request->user()->programs()
            ->withCount('events')
            ->with(['events' => fn ($query) => $query
                ->orderBy('event_date')
                ->orderBy('start_time')])
            ->orderBy('name')
            ->get();

        return view('field_officer.programs.index', compact('programs'));
    }

    public function activities(Request $request): View
    {
        $programIds = $request->user()->programs()->pluck('programs.id');

        $todayActivities = Event::query()
            ->whereIn('program_id', $programIds)
            ->whereDate('event_date', today())
            ->with('program:id,name')
            ->orderBy('start_time')
            ->get();

        return view('field_officer.activities.index', compact('todayActivities'));
    }

    public function followUps(Request $request): View
    {
        $filter = in_array($request->query('status'), ['open', 'completed', 'all'], true)
            ? $request->query('status')
            : 'open';

        $followUps = FollowUp::query()
            ->where('assigned_to', $request->user()->id)
            ->when($filter === 'open', fn ($query) => $query->whereNotIn('status', ['completed', 'cancelled']))
            ->when($filter === 'completed', fn ($query) => $query->where('status', 'completed'))
            ->with(['child:id,child_code,first_name,middle_name,last_name', 'event:id,title'])
            ->orderBy('next_follow_up_date')
            ->orderBy('follow_up_date')
            ->paginate(15)
            ->withQueryString();

        return view('field_officer.follow_ups.index', compact('followUps', 'filter'));
    }

    public function program(Request $request, Program $program): View
    {
        $program = $request->user()->programs()
            ->whereKey($program->id)
            ->with(['events' => fn ($query) => $query
                ->orderBy('event_date')
                ->orderBy('start_time')])
            ->firstOrFail();

        return view('field_officer.programs.show', compact('program'));
    }
}
