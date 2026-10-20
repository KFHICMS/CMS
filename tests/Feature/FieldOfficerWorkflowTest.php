<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\FollowUp;
use App\Models\Program;
use App\Models\QRCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_officer_sees_only_assigned_programs_and_activities(): void
    {
        $officer = $this->createUser('field_officer');
        $assignedProgram = $this->createProgram($officer, 'Assigned program');
        $assignedEvent = $this->createEvent($assignedProgram, 'Assigned activity');
        $unassignedProgram = Program::create([
            'name' => 'Private program',
            'status' => 'active',
        ]);
        $unassignedEvent = $this->createEvent($unassignedProgram, 'Private activity');

        $this->actingAs($officer)
            ->get(route('field_officer.dashboard'))
            ->assertOk()
            ->assertSee('Assigned program')
            ->assertSee('Assigned activity')
            ->assertDontSee('Private program')
            ->assertDontSee('Private activity');

        $this->get(route('field_officer.activities.show', $unassignedEvent))
            ->assertNotFound();
        $this->get(route('field_officer.programs.show', $unassignedProgram))
            ->assertNotFound();
        $this->get(route('field_officer.programs.show', $assignedProgram))
            ->assertOk()
            ->assertSee('Assigned activity');

        $this->get(route('child_officer.dashboard'))
            ->assertForbidden();
        $this->get(route('programs.index'))
            ->assertForbidden();
        $this->get(route('events.index'))
            ->assertForbidden();

        $this->actingAs($this->createUser('admin'))
            ->get(route('field_officer.dashboard'))
            ->assertForbidden();
    }

    public function test_field_officer_can_identify_save_submit_and_review_a_child_record(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $child = Child::create([
            'child_code' => 'KFHI-001',
            'first_name' => 'Asha',
            'last_name' => 'Perera',
            'status' => 'active',
        ]);
        $token = str_repeat('a', 64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
        ]);

        $this->get(route('qr.scan', $token))
            ->assertOk()
            ->assertDontSee('Asha Perera')
            ->assertDontSee('KFHI-001');

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'token' => url('/qr/scan/'.$token),
            ])
            ->assertOk()
            ->assertJsonPath('child.child_code', 'KFHI-001')
            ->assertJsonPath('child.name', 'Asha Perera')
            ->assertJsonMissingPath('child.gender')
            ->assertJsonMissingPath('child.date_of_birth');

        $this->get(route('field_officer.activities.show', $event))
            ->assertOk()
            ->assertSee('Scan Child QR Code')
            ->assertSee('KFHI-001');

        $this->post(route('field_officer.activities.save', $event), [
            'child_id' => $child->id,
            'attendance_status' => 'absent',
            'benefits' => [
                [
                    'item_name' => 'School supplies',
                    'category' => 'Education',
                    'quantity' => 2,
                    'received_at' => today()->format('Y-m-d'),
                    'notes' => 'Two notebooks',
                ],
            ],
            'observation_category' => 'Education',
            'observation_note' => 'Follow-up needed with the school.',
        ])
            ->assertRedirect();

        $submission = FieldActivitySubmission::query()->firstOrFail();
        $this->assertSame('draft', $submission->status);
        $this->assertDatabaseHas('field_activity_benefits', [
            'submission_id' => $submission->id,
            'item_name' => 'School supplies',
        ]);
        $this->assertDatabaseHas('field_activity_observations', [
            'submission_id' => $submission->id,
            'note' => 'Follow-up needed with the school.',
        ]);
        $this->assertDatabaseCount('attendances', 0);

        $this->post(route('field_officer.submissions.submit', $submission))
            ->assertRedirect(route('field_officer.submissions.review', $submission));

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'child_id' => $child->id,
            'marked_by' => $officer->id,
            'status' => 'absent',
        ]);
        $this->assertDatabaseHas('field_activity_submissions', [
            'id' => $submission->id,
            'status' => 'submitted',
        ]);

        $this->post(route('field_officer.submissions.submit', $submission))
            ->assertRedirect(route('field_officer.submissions.review', $submission));

        $this->assertSame(1, Attendance::query()
            ->where('event_id', $event->id)
            ->where('child_id', $child->id)
            ->count());

        $this->get(route('field_officer.submissions.review', $submission))
            ->assertOk()
            ->assertSee('School supplies')
            ->assertSee('Follow-up needed with the school.');
        $this->get(route('field_officer.reports.index'))
            ->assertOk()
            ->assertSee('KFHI-001')
            ->assertSee('Submitted');
    }

    public function test_expired_qr_codes_and_direct_child_id_submissions_are_rejected(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $child = Child::create([
            'child_code' => 'KFHI-002',
            'first_name' => 'Nimal',
            'last_name' => 'Silva',
            'status' => 'active',
        ]);
        $token = str_repeat('b', 64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'token' => url('/qr/scan/'.$token),
            ])
            ->assertNotFound();

        $this->post(route('field_officer.activities.save', $event), [
            'child_id' => $child->id,
            'attendance_status' => 'present',
        ])
            ->assertSessionHasErrors('child_id');

        $this->assertDatabaseCount('field_activity_submissions', 0);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_attendance_cannot_be_recorded_when_disabled(): void
    {
        [$officer, $event] = $this->assignedActivity(false);
        $child = Child::create([
            'child_code' => 'KFHI-003',
            'first_name' => 'Mala',
            'last_name' => 'Fernando',
            'status' => 'active',
        ]);
        $token = str_repeat('c', 64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'token' => url('/qr/scan/'.$token),
            ])
            ->assertOk();

        $this->post(route('field_officer.activities.save', $event), [
            'child_id' => $child->id,
            'attendance_status' => 'present',
        ])->assertSessionHasErrors('attendance_status');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_manual_child_code_can_record_present_attendance(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $child = Child::create([
            'child_code' => 'KFHI-005',
            'first_name' => 'Kamal',
            'last_name' => 'Silva',
            'status' => 'active',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'KFHI-005',
            ])
            ->assertOk()
            ->assertJsonPath('child.child_code', 'KFHI-005');

        $this->post(route('field_officer.activities.save', $event), [
            'child_id' => $child->id,
            'attendance_status' => 'present',
            'benefits' => [[
                'item_name' => '',
                'category' => '',
                'quantity' => 1,
                'received_at' => today()->format('Y-m-d'),
                'notes' => '',
            ]],
        ])->assertRedirect();

        $submission = FieldActivitySubmission::firstOrFail();
        $this->post(route('field_officer.submissions.submit', $submission))
            ->assertRedirect(route('field_officer.submissions.review', $submission));

        $this->assertDatabaseHas('attendances', [
            'event_id' => $event->id,
            'child_id' => $child->id,
            'status' => 'present',
            'method' => 'manual',
        ]);
    }

    public function test_field_officer_login_redirects_to_dashboard_and_inactive_accounts_are_denied(): void
    {
        $officer = $this->createUser('field_officer');

        $this->post(route('login.process'), [
            'username' => $officer->username,
            'password' => 'password',
        ])->assertRedirect(route('field_officer.dashboard'));

        $officer->update(['status' => 'inactive']);
        $this->actingAs($officer)
            ->get(route('field_officer.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_assign_programs_and_field_officers_can_update_only_their_follow_ups(): void
    {
        $admin = $this->createUser('admin');
        $officer = $this->createUser('field_officer');
        $otherOfficer = $this->createUser('field_officer');

        $this->actingAs($admin)
            ->get(route('programs.create'))
            ->assertOk()
            ->assertSee($officer->name);

        $this->post(route('programs.store'), [
            'name' => 'Health outreach',
            'status' => 'active',
            'officers' => [$officer->id],
        ])->assertRedirect(route('programs.index'));

        $program = Program::where('name', 'Health outreach')->firstOrFail();
        $this->assertTrue($program->officers()->whereKey($officer->id)->exists());
        $this->get(route('programs.index'))
            ->assertOk()
            ->assertSee('Health outreach')
            ->assertSee($officer->name);

        $this->post(route('events.store'), [
            'program_id' => $program->id,
            'event_code' => 'EVT-HEALTH-01',
            'title' => 'Community health visit',
            'event_date' => today()->format('Y-m-d'),
            'location' => 'Badulla',
        ])->assertRedirect(route('events.index'));

        $event = Event::where('event_code', 'EVT-HEALTH-01')->firstOrFail();
        $this->get(route('events.edit', $event))
            ->assertOk()
            ->assertSee('Health outreach');

        $this->assertDatabaseHas('events', [
            'event_code' => 'EVT-HEALTH-01',
            'program_id' => $program->id,
        ]);

        $child = Child::create([
            'child_code' => 'KFHI-004',
            'first_name' => 'Saman',
            'last_name' => 'Jayasinghe',
            'status' => 'active',
        ]);
        $followUp = FollowUp::create([
            'child_id' => $child->id,
            'assigned_to' => $officer->id,
            'category' => 'Education',
            'description' => 'Check school attendance.',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $this->actingAs($otherOfficer)
            ->get(route('field_officer.follow_ups.show', $followUp))
            ->assertForbidden();

        $this->actingAs($officer)
            ->patch(route('field_officer.follow_ups.update', $followUp), [
                'action_taken' => 'Met with the guardian.',
                'next_follow_up_date' => today()->addWeek()->format('Y-m-d'),
                'status' => 'completed',
            ])
            ->assertRedirect(route('field_officer.follow_ups.show', $followUp));
        $this->get(route('field_officer.follow_ups.show', $followUp))
            ->assertOk()
            ->assertSee('Met with the guardian.');

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'assigned_to' => $officer->id,
            'action_taken' => 'Met with the guardian.',
            'status' => 'completed',
        ]);
    }

    private function assignedActivity(bool $attendanceEnabled = true): array
    {
        $officer = $this->createUser('field_officer');
        $program = $this->createProgram($officer);
        $event = $this->createEvent($program, attendanceEnabled: $attendanceEnabled);

        return [$officer, $event];
    }

    private function createProgram(User $officer, string $name = 'Child support'): Program
    {
        $program = Program::create([
            'name' => $name,
            'status' => 'active',
        ]);
        $program->officers()->attach($officer);

        return $program;
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function createEvent(
        Program $program,
        string $title = 'Field visit',
        bool $attendanceEnabled = true
    ): Event {
        return Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'program_id' => $program->id,
            'title' => $title,
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => $attendanceEnabled,
            'enable_questionnaires' => false,
        ]);
    }
}
