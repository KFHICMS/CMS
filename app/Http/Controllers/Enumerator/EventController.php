<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Request $request, Event $event): View
    {
        $user = $request->user();

        abort_unless($event->enumerators()->whereKey($user->id)->exists(), 404);

        $event->load('program:id,name');
        $state = $event->collectionState();
        $collecting = $state === 'open';

        $questionnaires = ($collecting && $event->enable_questionnaires)
            ? Questionnaire::query()
                ->where('event_id', $event->id)
                ->where('status', 'published')
                ->orderBy('title')
                ->get(['id', 'title', 'description'])
            : collect();

        $mine = [
            'attendance' => Attendance::query()
                ->where('event_id', $event->id)
                ->where('marked_by', $user->id)
                ->count(),
            'questionnaires' => QuestionnaireResponse::query()
                ->where('submitted_by', $user->id)
                ->whereHas('questionnaire', fn ($query) => $query->where('event_id', $event->id))
                ->count(),
        ];

        return view('enumerator.events.show', compact('event', 'state', 'collecting', 'questionnaires', 'mine'));
    }
}
