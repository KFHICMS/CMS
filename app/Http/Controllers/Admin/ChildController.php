<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
<<<<<<< HEAD
use App\Services\ChildCustomFieldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
=======
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function index(Request $request): View
    {
        $query = Child::query();
    
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
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        $children = $query->latest()->get();
    
        return view('admin.children.index', compact('children'));
    }
<<<<<<< HEAD
    public function create(ChildCustomFieldService $customFields): View
    {
        return view('admin.children.create', [
            'customFields' => $customFields->activeFields(),
            'archivedCustomFields' => $customFields->archivedFields(),
            'customFieldValues' => [],
        ]);
=======
    public function create(): View
    {
        return view('admin.children.create');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_code' => [
                'required',
                'string',
                'max:50',
                'unique:children,child_code',
            ],
    
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
    
            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],
    
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
    
            'date_of_birth' => [
                'required',
                'date',
            ],
    
            'gender' => [
                'required',
                'in:male,female,other',
            ],
    
            'school_name' => [
                'nullable',
                'string',
                'max:255',
            ],
    
            'grade' => [
                'nullable',
                'string',
                'max:50',
            ],
    
            'address' => [
                'nullable',
                'string',
            ],
    
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],
    
            'notes' => [
                'nullable',
                'string',
            ],
            'profile_photo' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:2048',
],
        ]);
        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')
                ->store('children', 'public');
        }
<<<<<<< HEAD
        DB::transaction(function () use ($validated, $profilePhotoPath, $request) {
            $child = Child::create([
                'child_code' => $validated['child_code'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'school_name' => $validated['school_name'] ?? null,
                'grade' => $validated['grade'] ?? null,
                'address' => $validated['address'] ?? null,
                'district' => $validated['district'] ?? null,
                'gn_division' => $validated['gn_division'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'active',
                'created_by' => auth()->id(),
                'profile_photo' => $profilePhotoPath,
            ]);

            app(ChildCustomFieldService::class)->sync($child, $request);
        });
=======
        Child::create([
            'child_code' => $validated['child_code'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'school_name' => $validated['school_name'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'address' => $validated['address'] ?? null,
            'district' => $validated['district'] ?? null,
            'gn_division' => $validated['gn_division'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
            'created_by' => auth()->id(),
            'profile_photo' => $profilePhotoPath,
        ]);
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    
        return redirect()
            ->route('children.index')
            ->with('success', 'Child registered successfully.');
    }

    public function show(Child $child): View
    {
<<<<<<< HEAD
        $child->load('guardians', 'customFieldValues.customField');
=======
        $child->load('guardians');
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    
        return view('admin.children.show', compact('child'));
    }

<<<<<<< HEAD
    public function edit(Child $child, ChildCustomFieldService $customFields): View
    {
        return view('admin.children.edit', [
            'child' => $child,
            'customFields' => $customFields->activeFields(),
            'archivedCustomFields' => $customFields->archivedFields(),
            'customFieldValues' => $customFields->valuesFor($child),
        ]);
=======
    public function edit(Child $child): View
    {
        return view('admin.children.edit', compact('child'));
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    }

    public function update(Request $request, Child $child): RedirectResponse
{
    $validated = $request->validate([
        'child_code' => [
            'required',
            'string',
            'max:50',
            'unique:children,child_code,' . $child->id,
        ],

        'first_name' => [
            'required',
            'string',
            'max:100',
        ],

        'middle_name' => [
            'nullable',
            'string',
            'max:100',
        ],

        'last_name' => [
            'required',
            'string',
            'max:100',
        ],

        'date_of_birth' => [
            'required',
            'date',
        ],

        'gender' => [
            'required',
            'in:male,female,other',
        ],

        'school_name' => [
            'nullable',
            'string',
            'max:255',
        ],

        'grade' => [
            'nullable',
            'string',
            'max:50',
        ],

        'district' => [
            'nullable',
            'string',
            'max:100',
        ],
        'gn_division' => [
    'nullable',
    'string',
    'max:150',
],

        'address' => [
            'nullable',
            'string',
        ],

        'notes' => [
            'nullable',
            'string',
        ],

        'status' => [
            'required',
            'in:active,inactive',
        ],

        'profile_photo' => [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ],
    ]);

    $child->child_code = $validated['child_code'];
    $child->first_name = $validated['first_name'];
    $child->middle_name = $validated['middle_name'] ?? null;
    $child->last_name = $validated['last_name'];
    $child->date_of_birth = $validated['date_of_birth'];
    $child->gender = $validated['gender'];
    $child->school_name = $validated['school_name'] ?? null;
    $child->grade = $validated['grade'] ?? null;
    $child->district = $validated['district'] ?? null;
    $child->gn_division = $validated['gn_division'] ?? null;
    $child->address = $validated['address'] ?? null;
    $child->notes = $validated['notes'] ?? null;
    $child->status = $validated['status'];

    if ($request->hasFile('profile_photo')) {

        // Delete the old profile photo
        if ($child->profile_photo) {
            Storage::disk('public')->delete($child->profile_photo);
        }
    
        // Store the new profile photo
        $child->profile_photo = $request->file('profile_photo')
            ->store('children', 'public');
    }

<<<<<<< HEAD
    DB::transaction(function () use ($child, $request) {
        $child->save();
        app(ChildCustomFieldService::class)->sync($child, $request);
    });
=======
    $child->save();
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73

    return redirect()
        ->route('children.show', $child)
        ->with('success', 'Child information updated successfully.');
}
    public function destroy(Child $child): RedirectResponse
    {
        $child->forceDelete();
    
        return redirect()
            ->route('children.index')
            ->with('success', 'Child record permanently deleted.');
    }
}