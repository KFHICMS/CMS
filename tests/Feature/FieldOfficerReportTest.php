<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_include_only_the_officers_own_records(): void
    {
        [$officerA, $officerB, $event] = $this->twoOfficersOnOneActivity();
        $this->submissionFor($officerA, $event, 'KFHI-A01');
        $this->submissionFor($officerB, $event, 'KFHI-B01');

        $this->actingAs($officerA)
            ->get(route('field_officer.reports.index'))
            ->assertOk()
            ->assertViewHas('totals', fn (array $totals) => $totals['records'] === 1
                && $totals['submitted'] === 1
                && $totals['present'] === 1
                && $totals['absent'] === 0
                && $totals['benefit_items'] === 3
                && $totals['observations'] === 1);
    }

    public function test_csv_export_is_scoped_and_omits_child_names_and_note_text(): void
    {
        [$officerA, $officerB, $event] = $this->twoOfficersOnOneActivity();
        $this->submissionFor($officerA, $event, 'KFHI-A01');
        $this->submissionFor($officerB, $event, 'KFHI-B01');

        $response = $this->actingAs($officerA)
            ->get(route('field_officer.reports.export'))
            ->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('KFHI-A01', $csv);
        $this->assertStringNotContainsString('KFHI-B01', $csv);
        $this->assertStringNotContainsString('Hiddenfirst', $csv);
        $this->assertStringNotContainsString('private field note', $csv);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officerA->id,
            'action' => 'report_exported',
        ]);
    }

    private function twoOfficersOnOneActivity(): array
    {
        $officerA = $this->officer('a');
        $officerB = $this->officer('b');
        $program = Program::create(['name' => 'Child support', 'status' => 'active']);
        $program->officers()->attach([$officerA->id, $officerB->id]);
        $event = Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'program_id' => $program->id,
            'title' => 'Field visit',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => false,
        ]);

        return [$officerA, $officerB, $event];
    }

    private function officer(string $tag): User
    {
        return User::create([
            'name' => 'Officer '.$tag,
            'username' => 'fo-'.$tag.uniqid(),
            'email' => 'fo-'.$tag.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'field_officer',
            'status' => 'active',
        ]);
    }

    private function submissionFor(User $officer, Event $event, string $childCode): FieldActivitySubmission
    {
        $child = Child::create([
            'child_code' => $childCode,
            'first_name' => 'Hiddenfirst',
            'last_name' => 'Hiddenlast',
            'status' => 'active',
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

        $submission->benefits()->create(['item_name' => 'School bag', 'quantity' => 3]);
        $submission->observations()->create([
            'note' => 'private field note',
            'observed_at' => now(),
        ]);

        return $submission;
    }
}