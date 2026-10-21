<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\QuestionnaireResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $events = $user->assignedEvents()
            ->with('program:id,name')
            ->orderByDesc('event_date')
            ->orderBy('start_time')
            ->get();

        $todayEvents = $events
            ->filter(fn (Event $event) => $event->event_date->isToday())
            ->values();

        // Counting rules:
        // assigned       = events currently assigned to this enumerator
        // upcoming       = assigned events dated in the future (not cancelled/completed)
        // attendance     = attendance rows this enumerator recorded (unique per child+event)
        // questionnaires = questionnaire responses this enumerator submitted
        $stats = [
            'assigned' => $events->count(),
            'upcoming' => $events->filter(fn (Event $event) => $event->collectionState() === 'upcoming')->count(),
            'attendance' => Attendance::query()->where('marked_by', $user->id)->count(),
            'questionnaires' => QuestionnaireResponse::query()->where('submitted_by', $user->id)->count(),
        ];

        return view('enumerator.dashboard', compact('events', 'todayEvents', 'stats'));
    }
}