<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::latest('event_date')->get();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        $programs = Program::orderBy('name')->get(['id', 'name']);

        return view('admin.events.create', compact('programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => [
                'required',
                'integer',
                'exists:programs,id',
            ],
            'event_code' => [
                'required',
                'string',
                'max:50',
                'unique:events,event_code',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'event_date' => [
                'required',
                'date',
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'enable_attendance' => [
                'nullable',
                'boolean',
            ],
            'enable_questionnaires' => [
                'nullable',
                'boolean',
            ],
        ]);

        Event::create([
            'event_code' => $validated['event_code'],
            'program_id' => $validated['program_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'status' => 'planned',
        
            'enable_attendance' => $request->boolean('enable_attendance'),
            'enable_questionnaires' => $request->boolean('enable_questionnaires'),
        
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $programs = Program::orderBy('name')->get(['id', 'name']);

        return view('admin.events.edit', compact('event', 'programs'));
    }

    public function update(
        Request $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validate([
            'program_id' => [
                'nullable',
                'integer',
                'exists:programs,id',
            ],
            'event_code' => [
                'required',
                'string',
                'max:50',
                'unique:events,event_code,' . $event->id,
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'event_date' => [
                'required',
                'date',
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'end_time' => [
                'nullable',
                'date_format:H:i',
                'after:start_time',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                'in:planned,ongoing,completed,cancelled',
            ],
        ]);

        $event->update([
            'event_code' => $validated['event_code'],
            'program_id' => $validated['program_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
        ]);
        
                // Only change the switches when the edit form actually submitted them.
        if ($request->boolean('features_submitted')) {
            $event->update([
                'enable_attendance' => $request->boolean('enable_attendance'),
                'enable_questionnaires' => $request->boolean('enable_questionnaires'),
            ]);
        }

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->fieldActivitySubmissions()->exists()) {
            return back()->with(
                'error',
                'Activities with field submissions cannot be deleted.'
            );
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }
}