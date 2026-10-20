<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumeratorAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_scan_identifies_the_child_with_minimal_data_and_records_attendance(): void
    {
        [$enumerator, $event] = $this->assigned();
        $child = $this->child('KFHI-B01');
        $token = str_repeat('a', 64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
        ]);

        $this->actingAs($enumerator);

        $this->postJson(route('enumerator.events.attendance.identify', $event), [
            'token' => url('/qr/scan/'.$token),
        ])
            ->assertOk()
            ->assertJsonPath('child.child_code', 'KFHI-B01')
            ->assertJsonStructure(['child' => ['child_code', 'name']])
            ->assertJsonMissingPath('child.id')
            ->assertJsonMissingPath('child.photo_url');

        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertCreated();

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'child_id' => $child->id,
            'marked_by' => $enumerator->id,
            'method' => 'qr',
            'status' => 'present',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $enumerator->id,
            'action' => 'attendance_recorded',
        ]);
    }

    public function test_manual_code_lookup_can_record_absent(): void
    {
        [$enumerator, $event] = $this->assigned();
        $child = $this->child('KFHI-B02');

        $this->actingAs($enumerator);
        $this->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'KFHI-B02'])
            ->assertOk();
        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'absent'])
            ->assertCreated();

        $this->assertDatabaseHas('attendances', [
            'child_id' => $child->id,
            'method' => 'manual',
            'status' => 'absent',
        ]);
    }

    public function test_duplicate_attendance_is_rejected_and_counted_once(): void
    {
        [$enumerator, $event] = $this->assigned();
        $child = $this->child('KFHI-B03');
        Attendance::create([
            'event_id' => $event->id, 'child_id' => $child->id, 'marked_by' => $enumerator->id,
            'attendance_time' => now(), 'method' => 'manual', 'status' => 'present',
        ]);

        $this->actingAs($enumerator);
        $this->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'KFHI-B03'])
            ->assertOk()
            ->assertJsonPath('attendance.status', 'present');
        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Attendance has already been recorded for this child in this event.');

        $this->assertSame(1, Attendance::query()->where('child_id', $child->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'attendance_duplicate']);
    }

    public function test_same_child_can_attend_a_different_event(): void
    {
        [$enumerator, $event] = $this->assigned();
        $otherEvent = $this->event();
        $otherEvent->enumerators()->attach($enumerator);
        $child = $this->child('KFHI-B04');
        Attendance::create([
            'event_id' => $otherEvent->id, 'child_id' => $child->id, 'marked_by' => $enumerator->id,
            'attendance_time' => now(), 'method' => 'manual', 'status' => 'present',
        ]);

        $this->actingAs($enumerator);
        $this->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'KFHI-B04'])
            ->assertOk();
        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertCreated();
    }

    public function test_disabled_attendance_blocks_every_request(): void
    {
        [$enumerator, $event] = $this->assigned(['enable_attendance' => false]);
        $this->child('KFHI-B05');

        $this->actingAs($enumerator);
        $this->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'KFHI-B05'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Attendance has not been enabled for this event. Please contact your administrator.');
        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertForbidden();

        $this->assertSame(0, Attendance::query()->count());
    }

    public function test_events_that_are_closed_or_not_started_reject_attendance(): void
    {
        foreach ([
            ['status' => 'cancelled'],
            ['status' => 'completed'],
            ['event_date' => today()->addDay()],
        ] as $overrides) {
            [$enumerator, $event] = $this->assigned($overrides);
            $this->child('KFHI-C'.uniqid());

            $this->actingAs($enumerator)
                ->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'ANY'])
                ->assertStatus(409);
        }
    }

    public function test_unassigned_event_is_not_found(): void
    {
        $enumerator = $this->user('enumerator');
        $event = $this->event();

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.attendance.index', $event))
            ->assertNotFound();
        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'ANY'])
            ->assertNotFound();
        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertNotFound();
    }

    public function test_invalid_inputs_are_rejected_and_failures_are_audited_without_the_value(): void
    {
        [$enumerator, $event] = $this->assigned();

        $this->actingAs($enumerator);
        $this->postJson(route('enumerator.events.attendance.identify', $event), [
            'token' => url('/qr/scan/not-a-valid-token'),
        ])->assertStatus(422);
        $this->postJson(route('enumerator.events.attendance.identify', $event), [
            'child_code' => 'NO-SUCH-CODE',
        ])->assertNotFound();

        $logs = \App\Models\AuditLog::query()->where('action', 'identify_failed')->get();
        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertStringNotContainsString('not-a-valid-token', (string) $log->description);
            $this->assertStringNotContainsString('NO-SUCH-CODE', (string) $log->description);
        }

        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'present'])
            ->assertStatus(422);

        $this->child('KFHI-B06');
        $this->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'KFHI-B06'])
            ->assertOk();
        $this->postJson(route('enumerator.events.attendance.store', $event), ['status' => 'late'])
            ->assertStatus(422);
    }

    public function test_other_roles_and_inactive_enumerators_are_denied(): void
    {
        [$enumerator, $event] = $this->assigned();
        $inactive = $this->user('enumerator', 'inactive');
        $event->enumerators()->attach($inactive);

        $this->actingAs($this->user('field_officer'))
            ->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'ANY'])
            ->assertForbidden();

        $this->actingAs($inactive)
            ->postJson(route('enumerator.events.attendance.identify', $event), ['child_code' => 'ANY'])
            ->assertForbidden();
    }

    private function assigned(array $overrides = []): array
    {
        $enumerator = $this->user('enumerator');
        $event = $this->event($overrides);
        $event->enumerators()->attach($enumerator);

        return [$enumerator, $event];
    }

    private function child(string $code): Child
    {
        return Child::create([
            'child_code' => $code,
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
    }

    private function event(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'Field event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ], $overrides));
    }

    private function user(string $role, string $status = 'active'): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => $status,
        ]);
    }
}