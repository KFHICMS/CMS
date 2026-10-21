<?php

namespace App\Http\Controllers\Enumerator;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Services\AuditLogService;
use App\Services\QrIdentificationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private const SESSION_CHILD = 'enumerator_attendance_child.';
    private const SESSION_METHOD = 'enumerator_attendance_method.';

    public function __construct(
        private readonly AuditLogService $audit,
        private readonly QrIdentificationService $identifier
    ) {
    }

    public function index(Request $request, Event $event): View
    {
        $event = $this->authorizedEvent($request, $event);
        $unavailable = $this->unavailable($event);

        $recent = Attendance::query()
            ->where('event_id', $event->id)
            ->where('marked_by', $request->user()->id)
            ->with('child:id,child_code')
            ->latest('attendance_time')
            ->limit(20)
            ->get();

        return view('enumerator.attendance.index', compact('event', 'unavailable', 'recent'));
    }

    public function identify(Request $request, Event $event): JsonResponse
    {
        $event = $this->authorizedEvent($request, $event);

        if ($problem = $this->unavailable($event)) {
            return $this->fail($problem[1], $problem[0]);
        }

        $validated = $request->validate([
            'token' => ['nullable', 'required_without:child_code', 'string', 'max:2048'],
            'child_code' => ['nullable', 'required_without:token', 'string', 'max:30'],
        ]);

        $result = $this->identifier->resolve($validated['token'] ?? null, $validated['child_code'] ?? null);

        if (! $result['child']) {
            // The scanned value and any typed child code are deliberately not logged.
            $this->audit->record(
                $request,
                'identify_failed',
                'enumerator_attendance',
                $event->id,
                'Child identification failed: '.$result['reason'].'.'
            );

            return $this->fail($result['message'], $result['status']);
        }

        $child = $result['child'];

        $request->session()->put(self::SESSION_CHILD.$event->id, $child->id);
        $request->session()->put(self::SESSION_METHOD.$event->id, $result['method']);

        $existing = Attendance::query()
            ->where('event_id', $event->id)
            ->where('child_id', $child->id)
            ->first();

        return response()->json([
            'success' => true,
            'child' => [
                'child_code' => $child->child_code,
                'name' => trim(implode(' ', array_filter([
                    $child->first_name,
                    $child->middle_name,
                    $child->last_name,
                ]))),
            ],
            'attendance' => $existing ? ['status' => $existing->status] : null,
        ]);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $event = $this->authorizedEvent($request, $event);

        if ($problem = $this->unavailable($event)) {
            return $this->fail($problem[1], $problem[0]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:present,absent'],
        ]);

        $childId = $request->session()->get(self::SESSION_CHILD.$event->id);
        $child = $childId
            ? Child::query()->where('status', 'active')->find($childId)
            : null;

        if (! $child) {
            return $this->fail('Scan a child QR code or enter a child code first.', 422);
        }

        $method = $request->session()->get(self::SESSION_METHOD.$event->id, 'manual');

        $alreadyRecorded = Attendance::query()
            ->where('event_id', $event->id)
            ->where('child_id', $child->id)
            ->exists();

        if ($alreadyRecorded) {
            return $this->duplicate($request, $event, $child);
        }

        try {
            $attendance = Attendance::create([
                'event_id' => $event->id,
                'child_id' => $child->id,
                'marked_by' => $request->user()->id,
                'attendance_time' => now(),
                'method' => $method,
                'status' => $validated['status'],
            ]);
        } catch (UniqueConstraintViolationException) {
            // Two requests raced; the database unique index decided.
            return $this->duplicate($request, $event, $child);
        }

        $request->session()->forget([
            self::SESSION_CHILD.$event->id,
            self::SESSION_METHOD.$event->id,
        ]);

        $this->audit->record(
            $request,
            'attendance_recorded',
            'attendance',
            $attendance->id,
            'Attendance recorded by an enumerator.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved.',
            'record' => [
                'child_code' => $child->child_code,
                'status' => $attendance->status,
                'time' => now()->format('g:i A'),
            ],
        ], 201);
    }

    private function duplicate(Request $request, Event $event, Child $child): JsonResponse
    {
        $request->session()->forget([
            self::SESSION_CHILD.$event->id,
            self::SESSION_METHOD.$event->id,
        ]);

        $this->audit->record(
            $request,
            'attendance_duplicate',
            'attendance',
            $event->id,
            'Duplicate attendance attempt rejected.'
        );

        return $this->fail('Attendance has already been recorded for this child in this event.', 409);
    }

    private function authorizedEvent(Request $request, Event $event): Event
    {
        abort_unless($event->enumerators()->whereKey($request->user()->id)->exists(), 404);

        return $event;
    }

    /** @return array{0:int,1:string}|null */
    private function unavailable(Event $event): ?array
    {
        if (! $event->enable_attendance) {
            return [403, 'Attendance has not been enabled for this event. Please contact your administrator.'];
        }

        return match ($event->collectionState()) {
            'cancelled' => [409, 'This event has been cancelled. Attendance is not available.'],
            'completed' => [409, 'This event is completed. Attendance collection is closed.'],
            'upcoming' => [409, 'This event has not started yet. Attendance opens on '.$event->event_date->format('M j, Y').'.'],
            default => null,
        };
    }

    private function fail(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}