<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_identify_returns_the_child_photo_url_when_a_photo_exists(): void
    {
        [$officer, $event] = $this->assignedActivity();
        Child::create([
            'child_code' => 'KFHI-990',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
            'profile_photo' => 'children/test-photo.jpg',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'KFHI-990',
            ])
            ->assertOk()
            ->assertJsonPath('child.photo_url', asset('storage/children/test-photo.jpg'));
    }

    public function test_identify_returns_null_photo_when_the_child_has_none(): void
    {
        [$officer, $event] = $this->assignedActivity();
        Child::create([
            'child_code' => 'KFHI-991',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        $this->actingAs($officer)
            ->postJson(route('field_officer.activities.identify', $event), [
                'child_code' => 'KFHI-991',
            ])
            ->assertOk()
            ->assertJsonPath('child.photo_url', null);
    }

    public function test_officer_profile_photo_shows_on_the_dashboard_and_the_sidebar_pages(): void
    {
        [$officer] = $this->assignedActivity();
        $officer->update(['profile_photo' => 'profile-photos/officer.jpg']);
        $url = asset('storage/profile-photos/officer.jpg');

        $this->actingAs($officer)
            ->get(route('field_officer.dashboard'))
            ->assertOk()
            ->assertSee($url, false);

        $this->actingAs($officer)
            ->get(route('field_officer.programs.index'))
            ->assertOk()
            ->assertSee($url, false);
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