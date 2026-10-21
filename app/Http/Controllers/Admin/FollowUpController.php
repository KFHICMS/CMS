<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Program;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $followUps = FollowUp::query()
            ->with(['child:id,child_code', 'assignedTo:id,name', 'event:id,title,event_date'])
            ->when(
                in_array($status, ['pending', 'completed', 'cancelled'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.follow_ups.index', compact('followUps', 'status'));
    }

    public function create(): View
    {
        return view('admin.follow_ups.form', $this->formData(new FollowUp([
            'priority' => 'medium',
            'status' => 'pending',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(true));
        $this->ensureOfficerBelongsToActivity($validated);

        $child = Child::query()->where('child_code', $validated['child_code'])->firstOrFail();
        unset($validated['child_code']);

        $followUp = FollowUp::create($validated + [
            'child_id' => $child->id,
            'status' => 'pending',
        ]);

        $this->audit->record(
            $request,
            'created',
            'follow_up',
            $followUp->id,
            'Follow-up created by an administrator.'
        );

        return redirect()
            ->route('admin_follow_ups.index')
            ->with('success', 'Follow-up created.');
    }

    public function edit(FollowUp $followUp): View
    {
        $followUp->load('child:id,child_code');

        return view('admin.follow_ups.form', $this->formData($followUp));
    }

    public function update(Request $request, FollowUp $followUp): RedirectResponse
    {
        $validated = $request->validate($this->rules(false));
        $this->ensureOfficerBelongsToActivity($validated);

        $followUp->update($validated);

        $this->audit->record(
            $request,
            'updated',
            'follow_up',
            $followUp->id,
            'Follow-up updated by an administrator.'
        );

        return redirect()
            ->route('admin_follow_ups.index')
            ->with('success', 'Follow-up updated.');
    }

    private function rules(bool $creating): array
    {
        return [
            'child_code' => $creating
                ? ['required', 'string', 'max:30', Rule::exists('children', 'child_code')->where('status', 'active')]
                : ['prohibited'],
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'field_officer')->where('status', 'active'),
            ],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high'],
            'follow_up_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'status' => $creating ? ['prohibited'] : ['required', 'in:pending,completed,cancelled'],
        ];
    }

    /** A linked activity must belong to a program the assigned officer works on. */
    private function ensureOfficerBelongsToActivity(array $validated): void
    {
        if (empty($validated['event_id'])) {
            return;
        }

        $event = Event::query()->findOrFail($validated['event_id']);

        $belongs = $event->program_id && Program::query()
            ->whereKey($event->program_id)
            ->whereHas('officers', fn ($query) => $query->where('users.id', $validated['assigned_to']))
            ->exists();

        if (! $belongs) {
            throw ValidationException::withMessages([
                'event_id' => 'The assigned officer is not assigned to this activity\'s program. Assign the officer to the program first.',
            ]);
        }
    }

    private function formData(FollowUp $followUp): array
    {
        $officers = User::query()
            ->where('role', 'field_officer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        $events = Event::query()
            ->whereNotNull('program_id')
            ->where(fn ($query) => $query
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->when($followUp->event_id, fn ($q, $id) => $q->orWhere('id', $id)))
            ->orderByDesc('event_date')
            ->limit(200)
            ->get(['id', 'title', 'event_date']);

        return compact('followUp', 'officers', 'events');
    }
}