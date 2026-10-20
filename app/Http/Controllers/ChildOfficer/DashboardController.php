<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\FollowUp;

class DashboardController extends Controller
{
    public function index()
    {
        // Child statistics
        $totalChildren = Child::count();

        $activeChildren = Child::where('status', 'active')->count();

        $inactiveChildren = Child::where('status', 'inactive')->count();

        // Follow-up count
        $followUps = FollowUp::count();

        // Recently registered children
        $recentChildren = Child::latest()
            ->take(5)
            ->get();

        return view('child_officer.dashboard', compact(
            'totalChildren',
            'activeChildren',
            'inactiveChildren',
            'followUps',
            'recentChildren'
        ));
    }
}