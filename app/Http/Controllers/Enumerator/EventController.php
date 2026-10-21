<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Http\RedirectResponse;
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

    public function startQuestionnaire(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($event->enumerators()->whereKey($user->id)->exists(), 404);
        abort_unless(
            $questionnaire->event_id === $event->id
                && $event->enable_questionnaires
                && $questionnaire->status === 'published'
                && $event->collectionState() === 'open',
            404
        );

        $validated = $request->validate([
            'child_code' => ['required', 'string', 'max:30'],
        ]);

        $child = Child::query()
            ->where('child_code', $validated['child_code'])
            ->where('status', 'active')
            ->firstOrFail();

        return redirect()->route(
            'questionnaires.responses.create',
            [$questionnaire, $child]
        );
    }
}
