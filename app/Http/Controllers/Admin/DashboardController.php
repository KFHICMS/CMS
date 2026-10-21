<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\User;
use Carbon\Carbon;
=======
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
use Illuminate\Http\Request;

class DashboardController extends Controller
{
<<<<<<< HEAD
    /**
     * Display the admin dashboard with real-time statistics and summaries.
     */
    public function index()
    {
        $today = Carbon::today();

        // High-level statistics
        $totalChildren = Child::count();
        $totalUsers = User::count();
        $totalEvents = Event::count();
        $todayAttendance = Attendance::whereDate('attendance_time', $today)
            ->orWhereDate('created_at', $today)
            ->count();

        // Recent registrations & activities
        $recentChildren = Child::latest()->take(5)->get();
        $upcomingEvents = Event::where('event_date', '>=', $today)
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        // Follow-ups overview
        $pendingFollowUps = FollowUp::where('status', 'pending')->count();
        $inProgressFollowUps = FollowUp::where('status', 'in_progress')->count();
        $highPriorityFollowUps = FollowUp::whereIn('status', ['pending', 'in_progress'])
            ->where('priority', 'high')
            ->count();

        // Breakdown stats
        $activeChildrenCount = Child::where('status', 'active')->count();
        $completedFollowUps = FollowUp::where('status', 'completed')->count();

        return view('admin.dashboard', compact(
            'totalChildren',
            'totalUsers',
            'totalEvents',
            'todayAttendance',
            'recentChildren',
            'upcomingEvents',
            'pendingFollowUps',
            'inProgressFollowUps',
            'highPriorityFollowUps',
            'activeChildrenCount',
            'completedFollowUps'
        ));
    }
=======
    //
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
}
