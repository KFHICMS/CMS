<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventEnumeratorController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function edit(Event $event): View
    {
        $assignedIds = $event->enumerators()->pluck('users.id')->all();

        $enumerators = User::query()
            ->where('role', 'enumerator')
            ->where(fn ($query) => $query
                ->where('status', 'active')
                ->orWhereIn('id', $assignedIds))
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'status']);

        return view('admin.events.enumerators', compact('event', 'enumerators', 'assignedIds'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'enumerators' => ['nullable', 'array'],
            'enumerators.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', 'enumerator'),
            ],
        ]);

        $ids = array_map('intval', $validated['enumerators'] ?? []);
        $current = $event->enumerators()->pluck('users.id')->all();
        $added = array_diff($ids, $current);

        if ($added && User::query()->whereIn('id', $added)->where('status', '!=', 'active')->exists()) {
            throw ValidationException::withMessages([
                'enumerators' => 'Only active enumerators can be newly assigned.',
            ]);
        }

        $event->enumerators()->sync($ids);

        $this->audit->record(
            $request,
            'enumerators_assigned',
            'event',
            $event->id,
            'Event enumerator assignments updated by an administrator.'
        );

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Enumerator assignments updated.');
    }
}