<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerSidebarPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_sidebar_links_to_the_dedicated_pages(): void
    {
        $officer = $this->officer();

        $this->actingAs($officer)
            ->get(route('field_officer.dashboard'))
            ->assertOk()
            ->assertSee(route('field_officer.programs.index'))
            ->assertSee(route('field_officer.activities.index'))
            ->assertSee(route('field_officer.follow_ups.index'));
    }

    public function test_programs_page_lists_only_assigned_programs(): void
    {
        $officer = $this->officer();
        $mine = Program::create(['name' => 'Assigned program', 'status' => 'active']);
        Program::create(['name' => 'Other program', 'status' => 'active']);
        $mine->officers()->attach($officer);

        $this->actingAs($officer)
            ->get(route('field_officer.programs.index'))
            ->assertOk()
            ->assertSee('Assigned program')
            ->assertDontSee('Other program');
    }

    public function test_activities_page_lists_only_todays_assigned_activities(): void
    {
        $officer = $this->officer();
        $mine = Program::create(['name' => 'Assigned program', 'status' => 'active']);
        $other = Program::create(['name' => 'Other program', 'status' => 'active']);
        $mine->officers()->attach($officer);

        $this->event($mine, 'Today visit', today());
        $this->event($mine, 'Tomorrow visit', today()->addDay());
        $this->event($other, 'Unassigned visit', today());

        $this->actingAs($officer)
            ->get(route('field_officer.activities.index'))
            ->assertOk()
            ->assertSee('Today visit')
            ->assertDontSee('Tomorrow visit')
            ->assertDontSee('Unassigned visit');
    }

    public function test_follow_ups_page_shows_only_own_follow_ups_and_supports_filters(): void
    {
        $officer = $this->officer();
        $otherOfficer = $this->officer();
        $child = Child::create([
            'child_code' => 'KFHI-980',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        $this->followUp($child, $officer, 'Open category', 'pending');
        $this->followUp($child, $officer, 'Done category', 'completed');
        $this->followUp($child, $otherOfficer, 'Someone elses category', 'pending');

        $this->actingAs($officer)
            ->get(route('field_officer.follow_ups.index'))
            ->assertOk()
            ->assertSee('Open category')
            ->assertDontSee('Done category')
            ->assertDontSee('Someone elses category');

        $this->actingAs($officer)
            ->get(route('field_officer.follow_ups.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Done category')
            ->assertDontSee('Open category');
    }

    private function officer(): User
    {
        return User::create([
            'name' => 'Field Officer',
            'username' => 'fo-'.uniqid(),
            'email' => 'fo-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => 'field_officer',
            'status' => 'active',
        ]);
    }

    private function event(Program $program, string $title, $date): Event
    {
        return Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'program_id' => $program->id,
            'title' => $title,
            'event_date' => $date,
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => false,
        ]);
    }

    private function followUp(Child $child, User $officer, string $category, string $status): FollowUp
    {
        return FollowUp::create([
            'child_id' => $child->id,
            'assigned_to' => $officer->id,
            'category' => $category,
            'status' => $status,
            'priority' => 'medium',
        ]);
    }
}