<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
<<<<<<< HEAD
=======
use App\Models\Program;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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
<<<<<<< HEAD
        return view('admin.events.create');
=======
        $programs = Program::orderBy('name')->get(['id', 'name']);

        return view('admin.events.create', compact('programs'));
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
<<<<<<< HEAD
=======
            'program_id' => [
                'required',
                'integer',
                'exists:programs,id',
            ],
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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
<<<<<<< HEAD
=======
            'program_id' => $validated['program_id'] ?? null,
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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
<<<<<<< HEAD
        return view('admin.events.edit', compact('event'));
=======
        $programs = Program::orderBy('name')->get(['id', 'name']);

        return view('admin.events.edit', compact('event', 'programs'));
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }

    public function update(
        Request $request,
        Event $event
    ): RedirectResponse {
        $validated = $request->validate([
<<<<<<< HEAD
=======
            'program_id' => [
                'nullable',
                'integer',
                'exists:programs,id',
            ],
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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
<<<<<<< HEAD
=======
            'program_id' => $validated['program_id'] ?? null,
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
        ]);
<<<<<<< HEAD
=======
        
                // Only change the switches when the edit form actually submitted them.
        if ($request->boolean('features_submitted')) {
            $event->update([
                'enable_attendance' => $request->boolean('enable_attendance'),
                'enable_questionnaires' => $request->boolean('enable_questionnaires'),
            ]);
        }
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
<<<<<<< HEAD
=======
        if ($event->fieldActivitySubmissions()->exists()) {
            return back()->with(
                'error',
                'Activities with field submissions cannot be deleted.'
            );
        }

>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }
}