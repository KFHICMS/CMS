<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildInformation;
use Illuminate\Http\Request;

class ChildInformationController extends Controller
{
    /**
     * Show the Add New Information form.
     */
    public function create(Child $child)
    {
        return view(
            'child_officer.child_information.create',
            compact('child')
        );
    }

    /**
     * Store new information for a child.
     */
    public function store(Request $request, Child $child)
    {
        $validated = $request->validate([
            'information_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'information_date' => 'nullable|date',
        ]);

        $validated['child_id'] = $child->id;
        $validated['added_by'] = auth()->id();

        ChildInformation::create($validated);

        return redirect()
            ->route('child_officer.children.show', $child)
            ->with(
                'success',
                'Additional information added successfully.'
            );
    }
}