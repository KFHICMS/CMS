<?php

namespace App\Http\Controllers\FieldOfficer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AuditLogService;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\QRCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Database\UniqueConstraintViolationException;

class ActivityController extends Controller
{

    public function __construct(private readonly AuditLogService $audit)
    {
    }
    public function show(Request $request, Event $event): View
    {
        $event = $this->authorizedEvent($request, $event);
        $submission = null;

        if ($request->filled('submission')) {
            $submission = FieldActivitySubmission::query()
                ->with(['child', 'benefits', 'observations'])
                ->where('event_id', $event->id)
                ->where('user_id', $request->user()->id)
                ->findOrFail($request->integer('submission'));
        }

        $child = $submission?->child;

        if (! $child) {
            $identifiedChildId = $request->session()->get(
                'field_activity_child.'.$event->id
            );
            $child = $identifiedChildId
                ? Child::query()->where('status', 'active')->find($identifiedChildId)
                : null;
        }

        $existingAttendance = $child
            ? Attendance::query()
                ->where('event_id', $event->id)
                ->where('child_id', $child->id)
                ->first()
            : null;

        return view('field_officer.activities.show', compact(
            'event',
            'child',
            'submission',
            'existingAttendance'
        ));
    }

    public function identify(Request $request, Event $event): JsonResponse
    {
        $event = $this->authorizedEvent($request, $event);
        $this->ensureCollecting($event);

        $validated = $request->validate([
            'token' => [
                'nullable',
                'required_without:child_code',
                'string',
                'max:2048',
            ],
            'child_code' => [
                'nullable',
                'required_without:token',
                'string',
                'max:30',
            ],
        ]);

        $qrCode = null;
        if (! empty($validated['token'])) {
            $path = parse_url($validated['token'], PHP_URL_PATH);
            $prefix = '/qr/scan/';

            if (! is_string($path) || ! str_starts_with($path, $prefix)) {
                return $this->identifyFailed(
                    $request,
                    $event,
                    'unrecognised QR format',
                    'Use a valid KFHI child QR code.',
                    422
                );
            }

            $token = substr($path, strlen($prefix));
            if (! preg_match('/^[A-Za-z0-9]{64}$/D', $token)) {
                return $this->identifyFailed(
                    $request,
                    $event,
                    'malformed QR token',
                    'The QR code token is invalid.',
                    422
                );
            }

            $qrCode = QRCode::query()
                ->with('child')
                ->where('token_hash', hash('sha256', $token))
                ->where('status', 'active')
                ->where(fn ($query) => $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now()))
                ->first();

            if (! $qrCode || ! $qrCode->child) {
                return $this->identifyFailed(
                    $request,
                    $event,
                    'QR code invalid, expired, or revoked',
                    'This QR code is invalid, expired, or revoked.',
                    404
                );
            }

            $child = $qrCode->child;
        } else {
            $child = Child::query()
                ->where('child_code', $validated['child_code'])
                ->where('status', 'active')
                ->first();
        }

        if (! $child || $child->status !== 'active') {
            return $this->identifyFailed(
                $request,
                $event,
                $qrCode ? 'child inactive' : 'child code not found or inactive',
                'No active child was found for that identifier.',
                404
            );
        }

        if ($qrCode) {
            $qrCode->update(['last_scanned_at' => now()]);
        }

        $request->session()->put(
            'field_activity_child.'.$event->id,
            $child->id
        );
        $request->session()->put(
            'field_activity_identification_method.'.$event->id,
            $qrCode ? 'qr' : 'manual'
        );

        $this->audit->record(
            $request,
            'identified',
            'field_activity_qr',
            $event->id,
            'Child identified for an assigned activity.'
        );

        $fullName = trim(implode(' ', array_filter([
            $child->first_name,
            $child->middle_name,
            $child->last_name,
        ])));

        $existingAttendance = Attendance::query()
            ->where('event_id', $event->id)
            ->where('child_id', $child->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Child identified successfully.',
            'child' => [
                'id' => $child->id,
                'child_code' => $child->child_code,
                'name' => $fullName,
                'photo_url' => $child->profile_photo
                    ? asset('storage/'.$child->profile_photo)
                    : null,
            ],
            'attendance' => $existingAttendance
                ? ['status' => $existingAttendance->status]
                : null,
        ]);
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $event = $this->authorizedEvent($request, $event);
        $this->ensureCollecting($event);
        $this->filterEmptyBenefits($request);

        $validated = $request->validate([
            'child_id' => ['required', 'integer'],
            'attendance_status' => ['nullable', 'in:present,absent'],
            'benefits' => ['nullable', 'array', 'max:20'],
            'benefits.*.item_name' => ['required', 'string', 'max:255'],
            'benefits.*.category' => ['nullable', 'string', 'max:100'],
            'benefits.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'benefits.*.received_at' => ['nullable', 'date'],
            'benefits.*.notes' => ['nullable', 'string', 'max:2000'],
            'observation_category' => ['nullable', 'string', 'max:100'],
            'observation_note' => ['nullable', 'string', 'max:10000'],
        ]);

        $identifiedChildId = $request->session()->get(
            'field_activity_child.'.$event->id
        );
        $identificationMethod = $request->session()->get(
            'field_activity_identification_method.'.$event->id
        );
        if ((int) $identifiedChildId !== (int) $validated['child_id']) {
            throw ValidationException::withMessages([
                'child_id' => 'Identify this child again before saving field records.',
            ]);
        }
        if (! in_array($identificationMethod, ['qr', 'manual'], true)) {
            throw ValidationException::withMessages([
                'child_id' => 'Identify this child again before saving field records.',
            ]);
        }

        $child = Child::query()
            ->where('status', 'active')
            ->findOrFail($validated['child_id']);

        if (! $event->enable_attendance && ! empty($validated['attendance_status'])) {
            throw ValidationException::withMessages([
                'attendance_status' => 'Attendance is disabled for this activity.',
            ]);
        }

        $submission = DB::transaction(function () use (
            $event,
            $child,
            $request,
            $validated,
            $identificationMethod
        ): FieldActivitySubmission {
            $submission = FieldActivitySubmission::query()
                ->where('event_id', $event->id)
                ->where('child_id', $child->id)
                ->lockForUpdate()
                ->first();

            if ($submission && $submission->user_id !== $request->user()->id) {
                abort(403, 'This child activity record belongs to another officer.');
            }
            if ($submission?->status === 'submitted') {
                abort(409, 'This activity record has already been submitted.');
            }

            $existingAttendance = Attendance::query()
                ->where('event_id', $event->id)
                ->where('child_id', $child->id)
                ->first();

            if (
                $existingAttendance &&
                ! empty($validated['attendance_status']) &&
                $existingAttendance->status !== $validated['attendance_status']
            ) {
                throw ValidationException::withMessages([
                    'attendance_status' => 'Attendance is already recorded as '.
                        $existingAttendance->status.'.',
                ]);
            }

            $submission ??= new FieldActivitySubmission([
                'event_id' => $event->id,
                'child_id' => $child->id,
                'user_id' => $request->user()->id,
            ]);
            $submission->attendance_status = $validated['attendance_status'] ?? null;
            $submission->identification_method = $identificationMethod;
            $submission->status = 'draft';
            $submission->save();

            $submission->benefits()->delete();
            foreach ($validated['benefits'] ?? [] as $benefit) {
                $submission->benefits()->create($benefit);
            }

            $submission->observations()->delete();
            if (! empty($validated['observation_note'])) {
                $submission->observations()->create([
                    'category' => $validated['observation_category'] ?? null,
                    'note' => $validated['observation_note'],
                    'observed_at' => now(),
                ]);
            }

            $this->audit->record(
                $request,
                'draft_saved',
                'field_activity_submission',
                $submission->id,
                'Field activity draft saved.'
            );

            return $submission;
        });

        return redirect()
            ->route('field_officer.activities.show', [
                'event' => $event,
                'submission' => $submission->id,
            ])
            ->with('success', 'Draft saved. You can continue or submit when ready.');
    }

    public function submit(
        Request $request,
        FieldActivitySubmission $submission
    ): RedirectResponse {
        abort_unless($submission->user_id === $request->user()->id, 403);
        $event = $this->authorizedEvent($request, $submission->event);
        $this->ensureCollecting($event);

        DB::transaction(function () use ($request, $submission, $event): void {
            $lockedSubmission = FieldActivitySubmission::query()
                ->with(['benefits', 'observations'])
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if ($lockedSubmission->status === 'submitted') {
                return;
            }

            if (
                ! $lockedSubmission->attendance_status &&
                $event->enable_attendance &&
                ! Attendance::query()
                    ->where('event_id', $event->id)
                    ->where('child_id', $lockedSubmission->child_id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'attendance_status' => 'Record attendance before submitting this activity.',
                ]);
            }

            if (
                ! $lockedSubmission->attendance_status &&
                $lockedSubmission->benefits->isEmpty() &&
                $lockedSubmission->observations->isEmpty()
            ) {
                throw ValidationException::withMessages([
                    'submission' => 'Add attendance, a benefit, or an observation before submitting.',
                ]);
            }

            if ($lockedSubmission->attendance_status && ! $event->enable_attendance) {
                throw ValidationException::withMessages([
                    'attendance_status' => 'Attendance is disabled for this activity.',
                ]);
            }

            if ($lockedSubmission->attendance_status) {
                $attendance = Attendance::query()
                    ->where('event_id', $event->id)
                    ->where('child_id', $lockedSubmission->child_id)
                    ->lockForUpdate()
                    ->first();

                if ($attendance && $attendance->status !== $lockedSubmission->attendance_status) {
                    throw ValidationException::withMessages([
                        'attendance_status' => 'Attendance is already recorded as '.
                            $attendance->status.'.',
                    ]);
                }

                if (! $attendance) {
                    try {
                        Attendance::create([
                            'event_id' => $event->id,
                            'child_id' => $lockedSubmission->child_id,
                            'marked_by' => $request->user()->id,
                            'attendance_time' => now(),
                            'method' => $lockedSubmission->identification_method,
                            'status' => $lockedSubmission->attendance_status,
                        ]);
                    } catch (UniqueConstraintViolationException) {
                        $existing = Attendance::query()
                            ->where('event_id', $event->id)
                            ->where('child_id', $lockedSubmission->child_id)
                            ->first();

                        if (! $existing || $existing->status !== $lockedSubmission->attendance_status) {
                            throw ValidationException::withMessages([
                                'attendance_status' => 'Attendance for this child was just recorded by another officer'.
                                    ($existing ? ' as '.$existing->status : '').
                                    '. Please review it.',
                            ]);
                        }
                    }
                }
            }
            $lockedSubmission->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $this->audit->record(
                $request,
                'submitted',
                'field_activity_submission',
                $lockedSubmission->id,
                'Field activity record submitted.'
            );
        }, 3);

        return redirect()
            ->route('field_officer.submissions.review', $submission)
            ->with('success', 'Activity record submitted successfully.');
    }

    public function review(
        Request $request,
        FieldActivitySubmission $submission
    ): View {
        abort_unless($submission->user_id === $request->user()->id, 403);
        $event = $this->authorizedEvent($request, $submission->event);
                $submission->load(['child', 'benefits', 'observations', 'user', 'corrections']);
        $attendance = Attendance::query()
            ->where('event_id', $event->id)
            ->where('child_id', $submission->child_id)
            ->first();

        return view(
            'field_officer.activities.review',
            compact('event', 'submission', 'attendance')
        );
    }

    private function identifyFailed(
        Request $request,
        Event $event,
        string $reason,
        string $message,
        int $status
    ): JsonResponse {
        // The scanned value and any typed child code are deliberately not logged.
        $this->audit->record(
            $request,
            'identify_failed',
            'field_activity_qr',
            $event->id,
            'Child identification failed: '.$reason.'.'
        );

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private function authorizedEvent(Request $request, Event $event): Event
    {
        return Event::query()
            ->whereKey($event->id)
            ->whereHas(
                'program.officers',
                fn ($query) => $query->where('users.id', $request->user()->id)
            )
            ->with(['program.officers:id,name'])
            ->firstOrFail();
    }

    private function ensureCollecting(Event $event): void
    {
        abort_if(
            in_array($event->status, ['completed', 'cancelled'], true) ||
                in_array($event->program?->status, ['completed', 'cancelled'], true),
            409,
            'This activity is no longer open for field data collection.'
        );

        abort_if(
            $event->event_date && $event->event_date->copy()->startOfDay()->gt(now()->startOfDay()),
            409,
            'This activity has not started yet. Data collection opens on '.
                $event->event_date->format('d M Y').'.'
        );
    }

    private function filterEmptyBenefits(Request $request): void
    {
        $benefits = $request->input('benefits');
        if (! is_array($benefits)) {
            return;
        }

        $request->merge([
            'benefits' => array_values(array_filter(
                $benefits,
                fn ($benefit) => ! is_array($benefit) ||
                    filled($benefit['item_name'] ?? null) ||
                    filled($benefit['category'] ?? null) ||
                    filled($benefit['notes'] ?? null) ||
                    (
                        filled($benefit['quantity'] ?? null) &&
                        (int) $benefit['quantity'] !== 1
                    ) ||
                    (
                        filled($benefit['received_at'] ?? null) &&
                        $benefit['received_at'] !== today()->format('Y-m-d')
                    )
            )),
        ]);
    }
}
