<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\ChildInformation;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Guardian;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FollowUpManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_ups_can_be_created_for_every_supported_module(): void
    {
        $this->actingAs($this->createOfficer());

        $child = Child::create([
            'child_code' => 'CH-100',
            'first_name' => 'Alex',
            'last_name' => 'Child',
        ]);
        $guardian = Guardian::create([
            'child_id' => $child->id,
            'full_name' => 'Jordan Guardian',
        ]);
        $event = Event::create([
            'event_code' => 'EV-100',
            'title' => 'Community Event',
            'event_date' => today(),
        ]);
        $attendance = Attendance::create([
            'child_id' => $child->id,
            'event_id' => $event->id,
            'attendance_time' => now(),
        ]);
        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'Wellbeing Survey',
        ]);
        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => $child->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);
        $information = ChildInformation::create([
            'child_id' => $child->id,
            'information_type' => 'health',
            'title' => 'Health information review',
        ]);

        $targets = [
            'children' => $child,
            'guardians' => $guardian,
            'events' => $event,
            'attendance' => $attendance,
            'questionnaires' => $questionnaire,
            'questionnaire_responses' => $response,
            'child_information' => $information,
        ];

        foreach ($targets as $module => $target) {
            $this->post(route('admin.follow-ups.store'), [
                'module' => $module,
                'target_id' => $target->id,
                'description' => "Follow-up for {$module}",
                'priority' => 'medium',
            ])->assertRedirect(route('admin.follow-ups.index', ['module' => $module]));

            $this->assertDatabaseHas('follow_ups', [
                'target_type' => $target::class,
                'target_id' => $target->id,
                'child_id' => $target instanceof Child ? $child->id : null,
            ]);
            $this->assertSame(1, $target->followUps()->count());
        }

        $this->get(route('admin.follow-ups.index'))
            ->assertOk()
            ->assertSee('Community Event')
            ->assertSee('Wellbeing Survey')
            ->assertSee('Jordan Guardian')
            ->assertSee('Child Information');

        $this->get(route('admin.follow-ups.index', ['module' => 'attendance']))
            ->assertOk()
            ->assertSee('Community Event')
            ->assertSee('Attendance');
    }

    public function test_target_search_returns_records_for_a_selected_module(): void
    {
        $this->actingAs($this->createOfficer());
        Event::create([
            'event_code' => 'EV-SEARCH',
            'title' => 'Searchable Event',
            'event_date' => today(),
        ]);

        $this->getJson(route('admin.follow-ups.targets', [
            'module' => 'events',
            'search' => 'Searchable',
        ]))
            ->assertOk()
            ->assertJsonPath('data.0.label', 'Searchable Event (EV-SEARCH)');
    }

    public function test_active_users_in_each_configured_role_can_be_assigned(): void
    {
        $this->actingAs($this->createOfficer());

        $child = Child::create([
            'child_code' => 'CH-ASSIGN',
            'first_name' => 'Assign',
            'last_name' => 'Test',
        ]);
        $followUp = new FollowUp([
            'description' => 'Assignment test',
            'status' => 'pending',
            'priority' => 'medium',
        ]);
        $followUp->target()->associate($child);
        $followUp->save();

        foreach ([
            'admin',
            'child_officer',
            'field_officer',
            'enumerator',
        ] as $role) {
            $user = User::create([
                'name' => "Test {$role}",
                'email' => "{$role}@example.test",
                'password' => 'password',
                'role' => $role,
                'status' => 'active',
            ]);

            $this->patch(route('admin.follow-ups.update', $followUp), [
                'assigned_to' => $user->id,
                'status' => 'in_progress',
            ])->assertRedirect(route('admin.follow-ups.index'));

            $this->assertDatabaseHas('follow_ups', [
                'id' => $followUp->id,
                'assigned_to' => $user->id,
            ]);
        }

        $this->get(route('admin.follow-ups.index'))
            ->assertOk()
            ->assertSee('Test admin')
            ->assertSee('Test child_officer')
            ->assertSee('Test field_officer')
            ->assertSee('Test enumerator');
    }

    private function createOfficer(): User
    {
        return User::create([
            'name' => 'Test Officer',
            'email' => 'officer@example.test',
            'password' => 'password',
            'role' => 'child_officer',
            'status' => 'active',
        ]);
    }
}
