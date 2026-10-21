<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function create()
    {
        return view('child_officer.guardians.create');
    }

    public function searchChildren(Request $request)
    {
        $search = trim($request->get('search', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $children = Child::query()
            ->where(function ($query) use ($search) {
                $query->where('child_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->limit(10)
            ->get([
                'id',
                'child_code',
                'first_name',
                'middle_name',
                'last_name',
                'district',
                'gn_division',
            ]);

        return response()->json($children);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:children,id',
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:150',
            'guardian_type' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        Guardian::create($validated);

        return redirect()
            ->route('child_officer.children.show', $validated['child_id'])
            ->with('success', 'Guardian added successfully.');
    }

    public function edit(Guardian $guardian)
    {
        $guardian->load('child');

        return view(
            'child_officer.guardians.edit',
            compact('guardian')
        );
    }

    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:150',
            'guardian_type' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        $guardian->update($validated);

        return redirect()
            ->route('child_officer.children.show', $guardian->child_id)
            ->with('success', 'Guardian information updated successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        $childId = $guardian->child_id;

        $guardian->delete();

        return redirect()
            ->route('child_officer.children.show', $childId)
            ->with('success', 'Guardian information deleted successfully.');
    }
}