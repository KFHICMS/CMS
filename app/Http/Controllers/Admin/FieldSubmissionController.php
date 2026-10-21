<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FieldActivitySubmission;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FieldSubmissionController extends Controller
{
    public function __construct(private readonly AuditLogService $audit)
    {
    }

    public function index(): View
    {
        $submissions = FieldActivitySubmission::query()
            ->where('status', 'submitted')
            ->with(['event.program:id,name', 'child:id,child_code', 'user:id,name'])
            ->withCount('corrections')
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.field_submissions.index', compact('submissions'));
    }

    public function show(FieldActivitySubmission $submission): View
    {
        $submission->load([
            'event.program:id,name',
            'child',
            'user:id,name',
            'benefits',
            'observations',
            'corrections.corrector:id,name',
        ]);

        return view('admin.field_submissions.show', compact('submission'));
    }

    public function correct(Request $request, FieldActivitySubmission $submission): RedirectResponse
    {
        abort_unless(
            $submission->status === 'submitted',
            409,
            'Only submitted records can be corrected.'
        );

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'attendance_status' => ['nullable', 'in:present,absent'],
            'benefits' => ['nullable', 'array'],
            'benefits.*.item_name' => ['required', 'string', 'max:255'],
            'benefits.*.category' => ['nullable', 'string', 'max:100'],
            'benefits.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'benefits.*.notes' => ['nullable', 'string', 'max:2000'],
            'observations' => ['nullable', 'array'],
            'observations.*.category' => ['nullable', 'string', 'max:100'],
            'observations.*.note' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $submission, $validated): void {
            $locked = FieldActivitySubmission::query()
                ->with(['benefits', 'observations'])
                ->lockForUpdate()
                ->findOrFail($submission->id);

            abort_unless($locked->status === 'submitted', 409);

            $changes = [];

            $newAttendance = $validated['attendance_status'] ?? null;
            if (
                $locked->attendance_status !== null &&
                $newAttendance !== null &&
                $newAttendance !== $locked->attendance_status
            ) {
                $changes[] = [
                    'target' => 'attendance',
                    'id' => null,
                    'field' => 'status',
                    'old' => $locked->attendance_status,
                    'new' => $newAttendance,
                ];

                Attendance::query()
                    ->where('event_id', $locked->event_id)
                    ->where('child_id', $locked->child_id)
                    ->update(['status' => $newAttendance]);

                $locked->forceFill(['attendance_status' => $newAttendance])->save();
            }

            foreach ($locked->benefits as $benefit) {
                $input = $validated['benefits'][$benefit->id] ?? null;
                if (! $input) {
                    continue;
                }

                $updates = [];
                foreach (['item_name', 'category', 'quantity', 'notes'] as $field) {
                    $old = $benefit->{$field};
                    $new = $input[$field] ?? null;

                    if ($field === 'quantity') {
                        $old = (int) $old;
                        $new = (int) $new;
                    }

                    if ($new !== $old) {
                        $changes[] = [
                            'target' => 'benefit',
                            'id' => $benefit->id,
                            'field' => $field,
                            'old' => $old,
                            'new' => $new,
                        ];
                        $updates[$field] = $new;
                    }
                }

                if ($updates) {
                    $benefit->forceFill($updates)->save();
                }
            }

            foreach ($locked->observations as $observation) {
                $input = $validated['observations'][$observation->id] ?? null;
                if (! $input) {
                    continue;
                }

                $updates = [];
                foreach (['category', 'note'] as $field) {
                    $old = $observation->{$field};
                    $new = $input[$field] ?? null;

                    if ($new !== $old) {
                        $changes[] = [
                            'target' => 'observation',
                            'id' => $observation->id,
                            'field' => $field,
                            'old' => $old,
                            'new' => $new,
                        ];
                        $updates[$field] = $new;
                    }
                }

                if ($updates) {
                    $observation->forceFill($updates)->save();
                }
            }

            if ($changes === []) {
                throw ValidationException::withMessages([
                    'reason' => 'No changes were made. Edit a value before saving a correction.',
                ]);
            }

            $locked->corrections()->create([
                'corrected_by' => $request->user()->id,
                'reason' => $validated['reason'],
                'changes' => $changes,
            ]);

            // Values and field-note text are kept in the history table, not in the audit log.
            $this->audit->record(
                $request,
                'corrected',
                'field_activity_submission',
                $locked->id,
                'Submitted field activity record corrected by an administrator.'
            );
        });

        return redirect()
            ->route('field_submissions.show', $submission)
            ->with('success', 'Correction saved and added to the record history.');
    }
}