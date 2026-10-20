<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display all registered children.
     */
    public function index(Request $request)
    {
        $query = Child::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('child_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // District filter
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $children = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $districts = Child::query()
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');

        return view('child_officer.children.index', compact(
            'children',
            'districts'
        ));
    }

    /**
     * Display a specific child.
     */
    public function show(Child $child)
    {
        $child->load([
            'guardians',
            'qrCode',
            'digitalId',
            'followUps',
            'additionalInformation.addedBy',
        ]);
    
        return view(
            'child_officer.children.show',
            compact('child')
        );
    }
    /**
 * Show the child registration form.
 */
public function create()
{
    return view('child_officer.children.create');
}
/**
 * Store a newly registered child.
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'child_code' => 'required|string|max:100|unique:children,child_code',
        'first_name' => 'required|string|max:100',
        'middle_name' => 'nullable|string|max:100',
        'last_name' => 'required|string|max:100',
        'date_of_birth' => 'required|date',
        'gender' => 'required|string|max:20',
        'school_name' => 'nullable|string|max:255',
        'grade' => 'nullable|string|max:50',
        'address' => 'nullable|string',
        'district' => 'nullable|string|max:100',
        'gn_division' => 'nullable|string|max:150',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Profile Photo
    |--------------------------------------------------------------------------
    */

    if ($request->hasFile('profile_photo')) {
        $validated['profile_photo'] =
            $request->file('profile_photo')->store(
                'children',
                'public'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Created By
    |--------------------------------------------------------------------------
    */

    $validated['created_by'] = auth()->id();

    /*
    |--------------------------------------------------------------------------
    | Create Child
    |--------------------------------------------------------------------------
    */

    Child::create($validated);

    return redirect()
        ->route('child_officer.children.index')
        ->with('success', 'Child registered successfully.');
}

/**
 * Show the child edit form.
 */
public function edit(Child $child)
{
    return view('child_officer.children.edit', compact('child'));
}

/**
 * Update the child information.
 */
public function update(Request $request, Child $child)
{
    $validated = $request->validate([
        'child_code' => 'required|string|max:100|unique:children,child_code,' . $child->id,
        'first_name' => 'required|string|max:100',
        'middle_name' => 'nullable|string|max:100',
        'last_name' => 'required|string|max:100',
        'date_of_birth' => 'required|date',
        'gender' => 'required|string|max:20',
        'school_name' => 'nullable|string|max:255',
        'grade' => 'nullable|string|max:50',
        'address' => 'nullable|string',
        'district' => 'nullable|string|max:100',
        'gn_division' => 'nullable|string|max:150',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ]);

    if ($request->hasFile('profile_photo')) {

        $validated['profile_photo'] =
            $request->file('profile_photo')->store(
                'children',
                'public'
            );
    }

    $child->update($validated);

    return redirect()
        ->route('child_officer.children.show', $child)
        ->with('success', 'Child information updated successfully.');
}
public function destroy(Child $child)
{
    // Deactivate the child's active QR code
    if ($child->qrCode) {
        $child->qrCode->update([
            'status' => 'inactive',
        ]);
    }

    // Soft delete the child
    $child->delete();

    return redirect()
        ->route('child_officer.children.index')
        ->with('success', 'Child information deleted successfully.');
}





}
