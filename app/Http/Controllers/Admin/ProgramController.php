<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::withCount('events')
            ->with('officers:id,name')
            ->latest()
            ->paginate(15);

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        $officers = $this->activeOfficers();

        return view('admin.programs.create', compact('officers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProgram($request);

        DB::transaction(function () use ($validated): void {
            $program = Program::create([
                ...collect($validated)->except('officers')->all(),
                'created_by' => auth()->id(),
            ]);
            $program->officers()->sync($validated['officers'] ?? []);
        });

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program created and assigned successfully.');
    }

    public function edit(Program $program): View
    {
        $officers = $this->activeOfficers();
        $assignedOfficerIds = $program->officers()
            ->pluck('users.id')
            ->all();

        return view(
            'admin.programs.edit',
            compact('program', 'officers', 'assignedOfficerIds')
        );
    }

    public function update(
        Request $request,
        Program $program
    ): RedirectResponse {
        $validated = $this->validateProgram($request);

        DB::transaction(function () use ($program, $validated): void {
            $program->update(collect($validated)->except('officers')->all());
            $program->officers()->sync($validated['officers'] ?? []);
        });

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        if ($program->events()->exists()) {
            return back()->with(
                'error',
                'Programs with activities cannot be deleted. Mark the program completed instead.'
            );
        }

        $program->delete();

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program deleted successfully.');
    }

    private function validateProgram(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,active,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'officers' => ['nullable', 'array'],
            'officers.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $officerIds = $validated['officers'] ?? [];
        $activeOfficerIds = User::query()
            ->where('role', 'field_officer')
            ->where('status', 'active')
            ->whereIn('id', $officerIds)
            ->pluck('id')
            ->all();

        if (count($activeOfficerIds) !== count($officerIds)) {
            throw ValidationException::withMessages([
                'officers' => 'Only active Field Officers can be assigned.',
            ]);
        }

        return $validated;
    }

    private function activeOfficers()
    {
        return User::query()
            ->where('role', 'field_officer')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);
    }
}
