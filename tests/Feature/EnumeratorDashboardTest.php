<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnumeratorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_enumerator_login_redirects_to_the_dashboard(): void
    {
        $enumerator = $this->user('enumerator');

        $this->post(route('login.process'), [
            'username' => $enumerator->username,
            'password' => 'password',
        ])->assertRedirect(route('enumerator.dashboard'));
    }

    public function test_dashboard_shows_only_assigned_events_with_correct_counts(): void
    {
        $enumerator = $this->user('enumerator');
        $mine = $this->event(['title' => 'Assigned event']);
        $this->event(['title' => 'Unassigned event']);
        $future = $this->event(['title' => 'Future event', 'event_date' => today()->addDays(3)]);
        $mine->enumerators()->attach($enumerator);
        $future->enumerators()->attach($enumerator);

        $child = Child::create([
            'child_code' => 'KFHI-A10', 'first_name' => 'Test', 'last_name' => 'Child', 'status' => 'active',
        ]);
        Attendance::create([
            'event_id' => $mine->id, 'child_id' => $child->id, 'marked_by' => $enumerator->id,
            'attendance_time' => now(), 'method' => 'qr', 'status' => 'present',
        ]);
        $questionnaire = Questionnaire::create([
            'event_id' => $mine->id, 'title' => 'Survey', 'status' => 'published',
        ]);
        QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id, 'child_id' => $child->id,
            'submitted_by' => $enumerator->id, 'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($enumerator)
            ->get(route('enumerator.dashboard'))
            ->assertOk()
            ->assertSee('Assigned event')
            ->assertSee('Future event')
            ->assertDontSee('Unassigned event')
            ->assertViewHas('stats', fn (array $stats) => $stats['assigned'] === 2
                && $stats['upcoming'] === 1
                && $stats['attendance'] === 1
                && $stats['questionnaires'] === 1);
    }

    public function test_unassigned_event_workspace_is_not_found_and_assigned_one_opens(): void
    {
        $enumerator = $this->user('enumerator');
        $assigned = $this->event(['title' => 'Assigned event']);
        $other = $this->event(['title' => 'Other event']);
        $assigned->enumerators()->attach($enumerator);

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.show', $other))
            ->assertNotFound();

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.show', $assigned))
            ->assertOk()
            ->assertSee('Assigned event');
    }

    public function test_workspace_explains_disabled_options_and_closed_events(): void
    {
        $enumerator = $this->user('enumerator');
        $event = $this->event(['enable_attendance' => false, 'enable_questionnaires' => false]);
        $cancelled = $this->event(['status' => 'cancelled']);
        $event->enumerators()->attach($enumerator);
        $cancelled->enumerators()->attach($enumerator);

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.show', $event))
            ->assertOk()
            ->assertSee('Attendance has not been enabled for this event')
            ->assertSee('Questionnaires have not been enabled for this event');

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.show', $cancelled))
            ->assertOk()
            ->assertSee('This event has been cancelled');
    }

    public function test_only_active_enumerators_can_use_the_enumerator_area(): void
    {
        $this->actingAs($this->user('field_officer'))
            ->get(route('enumerator.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->user('admin'))
            ->get(route('enumerator.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->user('enumerator', 'inactive'))
            ->get(route('enumerator.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_assigns_only_active_enumerators(): void
    {
        $admin = $this->user('admin');
        $event = $this->event();
        $enumerator = $this->user('enumerator');
        $inactive = $this->user('enumerator', 'inactive');
        $officer = $this->user('field_officer');

        $this->actingAs($admin)
            ->put(route('events.enumerators.update', $event), ['enumerators' => [$enumerator->id]])
            ->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('event_user', ['event_id' => $event->id, 'user_id' => $enumerator->id]);

        $this->actingAs($admin)
            ->put(route('events.enumerators.update', $event), ['enumerators' => [$officer->id]])
            ->assertSessionHasErrors('enumerators.0');

        $this->actingAs($admin)
            ->put(route('events.enumerators.update', $event), ['enumerators' => [$enumerator->id, $inactive->id]])
            ->assertSessionHasErrors('enumerators');

        $this->assertDatabaseMissing('event_user', ['user_id' => $inactive->id]);
        $this->assertDatabaseMissing('event_user', ['user_id' => $officer->id]);
    }

    public function test_enumerator_cannot_change_assignments_or_event_options(): void
    {
        $enumerator = $this->user('enumerator');
        $event = $this->event();
        $event->enumerators()->attach($enumerator);

        $this->actingAs($enumerator)
            ->put(route('events.enumerators.update', $event), ['enumerators' => [$enumerator->id]])
            ->assertForbidden();

        $this->actingAs($enumerator)
            ->put(route('events.update', $event), [
                'event_code' => $event->event_code,
                'title' => $event->title,
                'event_date' => today()->toDateString(),
                'status' => 'planned',
                'features_submitted' => 1,
                'enable_attendance' => 0,
            ])
            ->assertForbidden();

        $this->assertTrue($event->fresh()->enable_attendance);
    }

    public function test_admin_can_change_event_switches_after_creation(): void
    {
        $admin = $this->user('admin');
        $event = $this->event(['enable_attendance' => true, 'enable_questionnaires' => true]);
        $payload = [
            'event_code' => $event->event_code,
            'title' => $event->title,
            'event_date' => today()->toDateString(),
            'status' => 'planned',
        ];

        // A request that does not carry the switches leaves them unchanged.
        $this->actingAs($admin)->put(route('events.update', $event), $payload);
        $this->assertTrue($event->fresh()->enable_attendance);
        $this->assertTrue($event->fresh()->enable_questionnaires);

        // The edit form carries them and applies the change.
        $this->actingAs($admin)->put(route('events.update', $event), $payload + [
            'features_submitted' => 1,
            'enable_questionnaires' => 1,
        ]);
        $this->assertFalse($event->fresh()->enable_attendance);
        $this->assertTrue($event->fresh()->enable_questionnaires);
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