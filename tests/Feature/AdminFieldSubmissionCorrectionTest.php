<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivityCorrection;
use App\Models\FieldActivitySubmission;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFieldSubmissionCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_correction_updates_values_and_keeps_history(): void
    {
        [$admin, , $submission, $benefit, $observation] = $this->submittedRecord();

        $this->actingAs($admin)
            ->patch(route('field_submissions.correct', $submission), [
                'reason' => 'Wrong quantity entered at the site.',
                'attendance_status' => 'absent',
                'benefits' => [$benefit->id => [
                    'item_name' => 'School bag',
                    'category' => null,
                    'quantity' => 5,
                    'notes' => null,
                ]],
                'observations' => [$observation->id => [
                    'category' => null,
                    'note' => 'private field note',
                ]],
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('field_activity_submissions', [
            'id' => $submission->id,
            'status' => 'submitted',
            'attendance_status' => 'absent',
        ]);
        $this->assertDatabaseHas('attendances', [
            'event_id' => $submission->event_id,
            'child_id' => $submission->child_id,
            'status' => 'absent',
        ]);
        $this->assertDatabaseHas('field_activity_benefits', [
            'id' => $benefit->id,
            'quantity' => 5,
        ]);

        $correction = FieldActivityCorrection::query()->firstOrFail();
        $this->assertSame($admin->id, $correction->corrected_by);
        $this->assertSame('Wrong quantity entered at the site.', $correction->reason);

        $quantity = collect($correction->changes)->firstWhere('field', 'quantity');
        $this->assertSame(3, $quantity['old']);
        $this->assertSame(5, $quantity['new']);

        $status = collect($correction->changes)->firstWhere('target', 'attendance');
        $this->assertSame('present', $status['old']);
        $this->assertSame('absent', $status['new']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'corrected',
            'record_id' => $submission->id,
        ]);
    }

    public function test_field_officer_cannot_correct_a_submitted_record(): void
    {
        [, $officer, $submission, $benefit] = $this->submittedRecord();

        $this->actingAs($officer)
            ->patch(route('field_submissions.correct', $submission), $this->unchangedPayload($submission, $benefit))
            ->assertForbidden();

        $this->assertSame(0, FieldActivityCorrection::query()->count());
    }

    public function test_draft_records_cannot_be_corrected(): void
    {
        [$admin, , $submission, $benefit] = $this->submittedRecord();
        $submission->update(['status' => 'draft']);

        $this->actingAs($admin)
            ->patch(route('field_submissions.correct', $submission), $this->unchangedPayload($submission, $benefit))
            ->assertStatus(409);
    }

    public function test_correction_without_any_change_is_rejected(): void
    {
        [$admin, , $submission, $benefit] = $this->submittedRecord();

        $this->actingAs($admin)
            ->patch(route('field_submissions.correct', $submission), $this->unchangedPayload($submission, $benefit))
            ->assertSessionHasErrors('reason');

        $this->assertSame(0, FieldActivityCorrection::query()->count());
    }

    public function test_reason_is_required(): void
    {
        [$admin, , $submission, $benefit] = $this->submittedRecord();
        $payload = $this->unchangedPayload($submission, $benefit);
        $payload['reason'] = '';
        $payload['benefits'][$benefit->id]['quantity'] = 9;

        $this->actingAs($admin)
            ->patch(route('field_submissions.correct', $submission), $payload)
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseHas('field_activity_benefits', [
            'id' => $benefit->id,
            'quantity' => 3,
        ]);
    }

    private function unchangedPayload(FieldActivitySubmission $submission, $benefit): array
    {
        return [
            'reason' => 'Checking that nothing changes.',
            'attendance_status' => $submission->attendance_status,
            'benefits' => [$benefit->id => [
                'item_name' => $benefit->item_name,
                'category' => $benefit->category,
                'quantity' => $benefit->quantity,
                'notes' => $benefit->notes,
            ]],
        ];
    }

    private function submittedRecord(): array
    {
        $admin = $this->user('admin');
        $officer = $this->user('field_officer');
        $program = Program::create(['name' => 'Child support', 'status' => 'active']);
        $program->officers()->attach($officer);
        $event = Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'program_id' => $program->id,
            'title' => 'Field visit',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => false,
        ]);
        $child = Child::create([
            'child_code' => 'KFHI-950',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        Attendance::create([
            'event_id' => $event->id,
            'child_id' => $child->id,
            'marked_by' => $officer->id,
            'attendance_time' => now(),
            'method' => 'manual',
            'status' => 'present',
        ]);

        $submission = FieldActivitySubmission::create([
            'event_id' => $event->id,
            'child_id' => $child->id,
            'user_id' => $officer->id,
            'status' => 'submitted',
            'attendance_status' => 'present',
            'identification_method' => 'manual',
            'submitted_at' => now(),
        ]);

        $benefit = $submission->benefits()->create(['item_name' => 'School bag', 'quantity' => 3]);
        $observation = $submission->observations()->create([
            'note' => 'private field note',
            'observed_at' => now(),
        ]);

        return [$admin, $officer, $submission, $benefit, $observation];
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }

        public function test_officer_review_page_shows_the_correction_notice(): void
    {
        [$admin, $officer, $submission] = $this->submittedRecord();

        $this->actingAs($officer)
            ->get(route('field_officer.submissions.review', $submission))
            ->assertOk()
            ->assertDontSee('Corrected by an administrator');

        FieldActivityCorrection::create([
            'submission_id' => $submission->id,
            'corrected_by' => $admin->id,
            'reason' => 'Wrong quantity entered at the site.',
            'changes' => [],
        ]);

        $this->actingAs($officer)
            ->get(route('field_officer.submissions.review', $submission))
            ->assertOk()
            ->assertSee('Corrected by an administrator')
            ->assertSee('Wrong quantity entered at the site.');
    }
}