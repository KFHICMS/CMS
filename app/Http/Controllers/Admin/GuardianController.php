<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guardian::with('child');
    
        if ($request->filled('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('alternate_phone', 'like', "%{$search}%")
                    ->orWhereHas('child', function ($childQuery) use ($search) {
                        $childQuery
                            ->where('child_code', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }
    
        if ($request->filled('relationship')) {
            $query->where('relationship', $request->relationship);
        }
    
        $guardians = $query->latest()->get();
    
        return view('admin.guardians.index', compact('guardians'));
    }

    public function create(): View
    {
        $children = Child::orderBy('first_name')->get();

        return view('admin.guardians.create', compact('children'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => [
                'required',
                'exists:children,id',
            ],
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'relationship' => [
                'required',
                'string',
                'max:100',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_primary' => [
                'nullable',
                'boolean',
            ],
        ]);
        if ($request->boolean('is_primary')) {
            Guardian::where('child_id', $validated['child_id'])
                ->update(['is_primary' => false]);
        }
        Guardian::create([
            'child_id' => $validated['child_id'],
            'full_name' => $validated['full_name'],
            'relationship' => $validated['relationship'],
            'phone' => $validated['phone'],
            'alternate_phone' => $validated['alternate_phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()
            ->route('guardians.index')
            ->with('success', 'Guardian registered successfully.');
    }

    public function show(Guardian $guardian): View
    {
        $guardian->load('child');

        return view('admin.guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian): View
    {
        $children = Child::orderBy('first_name')->get();

        return view('admin.guardians.edit', compact('guardian', 'children'));
    }

    public function update(
        Request $request,
        Guardian $guardian
    ): RedirectResponse {
        $validated = $request->validate([
            'child_id' => [
                'required',
                'exists:children,id',
            ],
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'relationship' => [
                'required',
                'string',
                'max:100',
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
            ],
            'alternate_phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_primary' => [
                'nullable',
                'boolean',
            ],
        ]);
        if ($request->boolean('is_primary')) {
            Guardian::where('child_id', $validated['child_id'])
                ->where('id', '!=', $guardian->id)
                ->update(['is_primary' => false]);
        }
        $guardian->update([
            'child_id' => $validated['child_id'],
            'full_name' => $validated['full_name'],
            'relationship' => $validated['relationship'],
            'phone' => $validated['phone'],
            'alternate_phone' => $validated['alternate_phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()
            ->route('guardians.show', $guardian)
            ->with('success', 'Guardian information updated successfully.');
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $guardian->delete();

        return redirect()
            ->route('guardians.index')
            ->with('success', 'Guardian deleted successfully.');
    }
}