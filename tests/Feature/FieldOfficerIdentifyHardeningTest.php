<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Child;
use App\Models\Event;
use App\Models\Program;
use App\Models\QRCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerIdentifyHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_qr_token_is_rejected_and_audited_without_logging_the_value(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $scanned = url('/qr/scan/not-a-valid-token');

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'token' => $scanned,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $log = AuditLog::query()->where('action', 'identify_failed')->firstOrFail();
        $this->assertSame($officer->id, $log->user_id);
        $this->assertSame('field_activity_qr', $log->module);
        $this->assertSame($event->id, $log->record_id);
        $this->assertStringNotContainsString('not-a-valid-token', (string) $log->description);
    }

    public function test_unknown_child_code_is_audited_without_logging_the_typed_code(): void
    {
        [$officer, $event] = $this->assignedActivity();

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'NO-SUCH-CODE',
            ])
            ->assertNotFound();

        $log = AuditLog::query()->where('action', 'identify_failed')->firstOrFail();
        $this->assertStringNotContainsString('NO-SUCH-CODE', (string) $log->description);
    }

    public function test_revoked_qr_code_is_audited_as_a_failure(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $child = Child::create([
            'child_code' => 'KFHI-901',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
        $token = str_repeat('d', 64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'inactive',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'token' => url('/qr/scan/'.$token),
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officer->id,
            'action' => 'identify_failed',
        ]);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'identified']);
    }

    public function test_successful_identification_is_still_audited(): void
    {
        [$officer, $event] = $this->assignedActivity();
        Child::create([
            'child_code' => 'KFHI-902',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'KFHI-902',
            ])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officer->id,
            'action' => 'identified',
            'record_id' => $event->id,
        ]);
    }

    public function test_identify_requests_are_throttled(): void
    {
        [$officer, $event] = $this->assignedActivity();
        $this->actingAs($officer);

        for ($i = 0; $i < 60; $i++) {
            $this->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'MISSING-'.$i,
            ])->assertNotFound();
        }

        $this->postJson(route('field_officer.activities.identify', $event), [
            'child_code' => 'MISSING-FINAL',
        ])->assertStatus(429);
    }

    private function assignedActivity(): array
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
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => false,
        ]);

        return [$officer, $event];
    }
}