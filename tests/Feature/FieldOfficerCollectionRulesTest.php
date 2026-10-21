<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerCollectionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_future_dated_activity_is_not_open_for_collection(): void
    {
        [$officer, $event] = $this->assignedActivity(today()->addDay());

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'ANY-CODE',
            ])
            ->assertStatus(409);
    }

    public function test_past_dated_planned_activity_stays_open_for_late_entry(): void
    {
        [$officer, $event] = $this->assignedActivity(today()->subDay());
        $this->activeChild('KFHI-903');

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'KFHI-903',
            ])
            ->assertOk();
    }

    public function test_completed_activity_is_closed(): void
    {
        [$officer, $event] = $this->assignedActivity(today(), 'completed');

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'ANY-CODE',
            ])
            ->assertStatus(409);
    }

    public function test_submit_refuses_attendance_that_conflicts_with_an_existing_record(): void
    {
        [$officer, $event] = $this->assignedActivity(today());
        $child = $this->activeChild('KFHI-904');

        Attendance::create([
            'event_id' => $event->id,
            'child_id' => $child->id,
            'marked_by' => $officer->id,
            'attendance_time' => now(),
            'method' => 'manual',
            'status' => 'absent',
        ]);

        $submission = FieldActivitySubmission::create([
            'event_id' => $event->id,
            'child_id' => $child->id,
            'user_id' => $officer->id,
            'status' => 'draft',
            'attendance_status' => 'present',
            'identification_method' => 'manual',
        ]);

        $this->actingAs($officer)
            ->post(route('field_officer.submissions.submit', $submission))
            ->assertSessionHasErrors('attendance_status');

        $this->assertDatabaseHas('field_activity_submissions', [
            'id' => $submission->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'child_id' => $child->id,
            'status' => 'absent',
        ]);
    }

    private function activeChild(string $code): Child
    {
        return Child::create([
            'child_code' => $code,
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
    }

    private function assignedActivity($date, string $status = 'planned'): array
    {
        $officer = User::create([
            'name' => 'Field Officer',
            'username' => 'fo-'.uniqid(),
            'email' => 'fo-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'field_officer',
            'status' => 'active',
        ]);
        $program = Program::create(['name' => 'Child support', 'status' => 'active']);
        $program->officers()->attach($officer);
        $event = Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'program_id' => $program->id,
            'title' => 'Field visit',
            'event_date' => $date,
            'status' => $status,
            'enable_attendance' => true,
            'enable_questionnaires' => false,
        ]);

        return [$officer, $event];
    }
}