<?php

namespace App\Http\Controllers\FieldOfficer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FollowUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function show(Request $request, FollowUp $followUp): View
    {
        $followUp = $this->assignedFollowUp($request, $followUp);
                $followUp->load([
            'child:id,child_code,first_name,middle_name,last_name',
            'event:id,program_id,title,event_date',
        ]);

        $canOpenActivity = $followUp->event
            && $request->user()->programs()->whereKey($followUp->event->program_id)->exists();

        return view('field_officer.follow_ups.show', compact('followUp', 'canOpenActivity'));
    }

    public function update(
        Request $request,
        FollowUp $followUp
    ): RedirectResponse {
        $followUp = $this->assignedFollowUp($request, $followUp);
        $validated = $request->validate([
            'action_taken' => ['nullable', 'string', 'max:10000'],
            'next_follow_up_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,completed'],
        ]);

        if ($validated['status'] === 'completed' && blank($validated['action_taken'])) {
            return back()
                ->withErrors([
                    'action_taken' => 'Describe the action taken before completing this follow-up.',
                ])
                ->withInput();
        }

        $followUp->update($validated);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'updated',
            'module' => 'follow_up',
            'record_id' => $followUp->id,
            'description' => 'Assigned follow-up updated by its Field Officer.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('field_officer.follow_ups.show', $followUp)
            ->with('success', 'Follow-up updated successfully.');
    }

    private function assignedFollowUp(
        Request $request,
        FollowUp $followUp
    ): FollowUp {
        abort_unless($followUp->assigned_to === $request->user()->id, 403);

        return $followUp;
    }
}
