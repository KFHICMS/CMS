<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_follow_up_linked_to_an_activity(): void
    {
        [$admin, $officer, $event, $child] = $this->setUpData(true);

        $this->actingAs($admin)
            ->post(route('admin_follow_ups.store'), $this->payload($child, $officer, $event))
            ->assertRedirect(route('admin_follow_ups.index'));

        $this->assertDatabaseHas('follow_ups', [
            'child_id' => $child->id,
            'assigned_to' => $officer->id,
            'event_id' => $event->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'created',
            'module' => 'follow_up',
        ]);
    }

    public function test_activity_must_belong_to_a_program_the_officer_works_on(): void
    {
        [$admin, $officer, $event, $child] = $this->setUpData(false);

        $this->actingAs($admin)
            ->post(route('admin_follow_ups.store'), $this->payload($child, $officer, $event))
            ->assertSessionHasErrors('event_id');

        $this->assertSame(0, FollowUp::query()->count());
    }

    public function test_unknown_child_code_is_rejected(): void
    {
        [$admin, $officer, $event, $child] = $this->setUpData(true);
        $payload = $this->payload($child, $officer, $event);
        $payload['child_code'] = 'NO-SUCH-CODE';

        $this->actingAs($admin)
            ->post(route('admin_follow_ups.store'), $payload)
            ->assertSessionHasErrors('child_code');
    }

    public function test_field_officer_cannot_use_the_admin_follow_up_pages(): void
    {
        [, $officer, $event, $child] = $this->setUpData(true);

        $this->actingAs($officer)
            ->get(route('admin_follow_ups.create'))
            ->assertForbidden();

        $this->actingAs($officer)
            ->post(route('admin_follow_ups.store'), $this->payload($child, $officer, $event))
            ->assertForbidden();
    }

    public function test_officer_sees_the_related_activity_on_their_follow_up(): void
    {
        [, $officer, $event, $child] = $this->setUpData(true);

        $followUp = FollowUp::create([
            'child_id' => $child->id,
            'assigned_to' => $officer->id,
            'event_id' => $event->id,
            'category' => 'Health check',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $this->actingAs($officer)
            ->get(route('field_officer.follow_ups.show', $followUp))
            ->assertOk()
            ->assertSee('Related activity')
            ->assertSee('Field visit')
            ->assertSee(route('field_officer.activities.show', $event));
    }

    private function payload(Child $child, User $officer, Event $event): array
    {
        return [
            'child_code' => $child->child_code,
            'assigned_to' => $officer->id,
            'event_id' => $event->id,
            'category' => 'Health check',
            'description' => 'Visit the family and confirm check-up.',
            'priority' => 'high',
            'follow_up_date' => today()->toDateString(),
            'next_follow_up_date' => today()->addDays(7)->toDateString(),
        ];
    }

    private function setUpData(bool $officerInProgram): array
    {
        $admin = $this->user('admin');
        $officer = $this->user('field_officer');
        $program = Program::create(['name' => 'Child support', 'status' => 'active']);

        if ($officerInProgram) {
            $program->officers()->attach($officer);
        }

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
            'child_code' => 'KFHI-970',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        return [$admin, $officer, $event, $child];
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
}