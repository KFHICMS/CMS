<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Questionnaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function index(Event $event): View
    {
        $questionnaires = Questionnaire::where('event_id', $event->id)
            ->withCount('questions')
            ->latest()
            ->get();

        return view(
            'admin.events.questionnaires.index',
            compact('event', 'questionnaires')
        );
    }

    public function create(Event $event): View
    {
        return view(
            'admin.events.questionnaires.create',
            compact('event')
        );
    }

    public function store(
        Request $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Questionnaire::create([
            'event_id' => $event->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('events.questionnaires.index', $event)
            ->with('success', 'Questionnaire created successfully.');
    }

    public function show(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $questionnaire->load([
            'questions.options',
        ]);

        return view(
            'admin.events.questionnaires.show',
            compact('event', 'questionnaire')
        );
    }

    public function edit(
        Event $event,
        Questionnaire $questionnaire
    ): View {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        return view(
            'admin.events.questionnaires.edit',
            compact('event', 'questionnaire')
        );
    }

    public function update(
        Request $request,
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:draft,published,closed',
            ],
        ]);

        $questionnaire->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route(
                'events.questionnaires.show',
                [$event, $questionnaire]
            )
            ->with('success', 'Questionnaire updated successfully.');
    }

    public function destroy(
        Event $event,
        Questionnaire $questionnaire
    ): RedirectResponse {
        abort_unless(
            $questionnaire->event_id === $event->id,
            404
        );

        $questionnaire->delete();

        return redirect()
            ->route('events.questionnaires.index', $event)
            ->with('success', 'Questionnaire deleted successfully.');
    }
}