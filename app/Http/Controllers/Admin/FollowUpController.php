<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Attendance;
use App\Models\Child;
use App\Models\ChildInformation;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Guardian;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    private const ASSIGNABLE_ROLES = [
        'admin',
        'child_officer',
        'field_officer',
        'enumerator',
    ];

    private const MODULES = [
        'children' => ['label' => 'Child Management', 'model' => Child::class],
        'guardians' => ['label' => 'Guardians', 'model' => Guardian::class],
        'events' => ['label' => 'Events', 'model' => Event::class],
        'attendance' => ['label' => 'Attendance', 'model' => Attendance::class],
        'questionnaires' => ['label' => 'Questionnaires', 'model' => Questionnaire::class],
        'questionnaire_responses' => ['label' => 'Questionnaire Responses', 'model' => QuestionnaireResponse::class],
        'child_information' => ['label' => 'Child Information', 'model' => ChildInformation::class],
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                'pending',
                'in_progress',
                'completed',
                'overdue',
                'unassigned',
            ])],
            'module' => ['nullable', Rule::in(array_keys(self::MODULES))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $today = today();
        $followUps = FollowUp::query()
            ->with(['target', 'assignedTo'])
            ->when($filters['status'] ?? null, function ($query, $status) use ($today) {
                if ($status === 'overdue') {
                    $query->where('status', '!=', 'completed')
                        ->whereNotNull('next_follow_up_date')
                        ->whereDate('next_follow_up_date', '<', $today);
                } elseif ($status === 'unassigned') {
                    $query->whereNull('assigned_to')
                        ->where('status', '!=', 'completed');
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['module'] ?? null, function ($query, $module) {
                $query->whereHasMorph('target', self::MODULES[$module]['model']);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $this->applySearch($query, $search);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'open' => FollowUp::where('status', '!=', 'completed')->count(),
            'unassigned' => FollowUp::where('status', '!=', 'completed')
                ->whereNull('assigned_to')
                ->count(),
            'inProgress' => FollowUp::where('status', 'in_progress')->count(),
            'overdue' => FollowUp::where('status', '!=', 'completed')
                ->whereNotNull('next_follow_up_date')
                ->whereDate('next_follow_up_date', '<', $today)
                ->count(),
        ];

        $officers = User::query()
            ->whereIn('role', self::ASSIGNABLE_ROLES)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $modules = collect(self::MODULES)
            ->mapWithKeys(fn (array $module, string $key) => [$key => $module['label']]);

        return view('admin.follow-ups.index', compact(
            'followUps',
            'stats',
            'officers',
            'modules'
        ));
    }

    public function targets(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', Rule::in(array_keys(self::MODULES))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $module = $validated['module'];
        $search = trim($validated['search'] ?? '');
        $model = self::MODULES[$module]['model'];
        $query = $model::query();

        switch ($module) {
            case 'children':
                $query->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('child_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                }));
                break;
            case 'guardians':
                $query->with('child')
                    ->when($search !== '', fn ($q) => $q->where('full_name', 'like', "%{$search}%"));
                break;
            case 'events':
                $query->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('event_code', 'like', "%{$search}%");
                }));
                break;
            case 'attendance':
                $query->with(['child', 'event'])
                    ->when($search !== '', fn ($q) => $q->whereHas('child', function ($q) use ($search) {
                        $q->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('event', fn ($q) => $q->where('title', 'like', "%{$search}%")));
                break;
            case 'questionnaires':
                $query->with('event')
                    ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"));
                break;
            case 'questionnaire_responses':
                $query->with(['child', 'questionnaire'])
                    ->when($search !== '', fn ($q) => $q->whereHas('child', function ($q) use ($search) {
                        $q->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('questionnaire', fn ($q) => $q->where('title', 'like', "%{$search}%")));
                break;
            case 'child_information':
                $query->with('child')
                    ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('child', fn ($q) => $q->where('child_code', 'like', "%{$search}%")));
                break;
        }

        $records = $query->latest()
            ->limit(30)
            ->get()
            ->map(fn ($record) => [
                'id' => $record->getKey(),
                'label' => $this->targetOptionLabel($module, $record),
            ]);

        return response()->json(['data' => $records]);
    }

    public function store(Request $request)
    {
        $module = $request->input('module');
        $model = is_string($module) && isset(self::MODULES[$module])
            ? self::MODULES[$module]['model']
            : null;

        $validated = $request->validate([
            'module' => ['required', Rule::in(array_keys(self::MODULES))],
            'target_id' => [
                'required',
                'integer',
                Rule::exists($model ? (new $model())->getTable() : 'children', 'id'),
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->whereIn('role', self::ASSIGNABLE_ROLES)
                    ->where('status', 'active'),
            ],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:5000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $target = $model::query()->findOrFail($validated['target_id']);

        $followUp = new FollowUp([
            'child_id' => $target instanceof Child ? $target->getKey() : null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'follow_up_date' => today(),
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'],
            'status' => 'pending',
            'priority' => $validated['priority'],
        ]);
        $followUp->target()->associate($target);
        $followUp->save();

        return redirect()
            ->route('admin.follow-ups.index', ['module' => $module])
            ->with('success', 'Follow-up created successfully.');
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $validated = $request->validate([
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->whereIn('role', self::ASSIGNABLE_ROLES)
                    ->where('status', 'active'),
            ],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'action_taken' => ['nullable', 'string', 'max:5000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'filter_status' => ['nullable', Rule::in([
                'pending',
                'in_progress',
                'completed',
                'overdue',
                'unassigned',
            ])],
            'filter_module' => ['nullable', Rule::in(array_keys(self::MODULES))],
            'filter_search' => ['nullable', 'string', 'max:100'],
        ]);

        $returnFilters = array_filter([
            'status' => $validated['filter_status'] ?? null,
            'module' => $validated['filter_module'] ?? null,
            'search' => $validated['filter_search'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');
        unset(
            $validated['filter_status'],
            $validated['filter_module'],
            $validated['filter_search']
        );

        $followUp->update($validated);

        return redirect()
            ->route('admin.follow-ups.index', $returnFilters)
            ->with('success', 'Follow-up updated successfully.');
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query->where('category', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('assignedTo', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHasMorph('target', array_column(self::MODULES, 'model'), function (
                    Builder $q,
                    string $type
                ) use ($search) {
                    if ($type === Child::class) {
                        $q->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    } elseif ($type === Guardian::class) {
                        $q->where('full_name', 'like', "%{$search}%");
                    } elseif ($type === Event::class) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('event_code', 'like', "%{$search}%");
                    } elseif ($type === Attendance::class) {
                        $q->whereHas('child', fn (Builder $child) => $child
                            ->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%"))
                            ->orWhereHas('event', fn (Builder $event) => $event
                                ->where('title', 'like', "%{$search}%"));
                    } elseif ($type === Questionnaire::class) {
                        $q->where('title', 'like', "%{$search}%");
                    } elseif ($type === QuestionnaireResponse::class) {
                        $q->whereHas('child', fn (Builder $child) => $child
                            ->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%"))
                            ->orWhereHas('questionnaire', fn (Builder $questionnaire) => $questionnaire
                                ->where('title', 'like', "%{$search}%"));
                    } elseif ($type === ChildInformation::class) {
                        $q->where('title', 'like', "%{$search}%");
                    }
                });
        });
    }

    private function targetOptionLabel(string $module, object $record): string
    {
        return match ($module) {
            'children' => trim(implode(' ', array_filter([
                $record->first_name,
                $record->middle_name,
                $record->last_name,
            ]))) . ' (' . $record->child_code . ')',
            'guardians' => $record->full_name . ' — ' . ($record->child?->child_code ?? 'Child unavailable'),
            'events' => $record->title . ' (' . $record->event_code . ')',
            'attendance' => ($record->child?->child_code ?? 'Child unavailable')
                . ' — ' . ($record->event?->title ?? 'Event unavailable'),
            'questionnaires' => $record->title
                . ($record->event ? ' — ' . $record->event->title : ''),
            'questionnaire_responses' => ($record->child?->child_code ?? 'Child unavailable')
                . ' — ' . ($record->questionnaire?->title ?? 'Questionnaire response'),
            'child_information' => $record->title
                . ' — ' . ($record->child?->child_code ?? 'Child unavailable'),
        };
    }
}
=======
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
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
