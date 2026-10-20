<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerBreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_show_breadcrumbs_with_program_and_activity_context(): void
    {
        [$officer, $program, $event] = $this->assignedActivity();

        $this->actingAs($officer)
            ->get(route('field_officer.reports.index'))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('Back to Dashboard');

        $this->actingAs($officer)
            ->get(route('field_officer.programs.show', $program))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('Child support');

        $this->actingAs($officer)
            ->get(route('field_officer.activities.show', $event))
            ->assertOk()
            ->assertSee('Back to Child support')
            ->assertSee('Field visit');
    }

    public function test_activity_breadcrumb_shows_the_identified_child(): void
    {
        [$officer, , $event] = $this->assignedActivity();
        $child = Child::create([
            'child_code' => 'KFHI-960',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        $this->actingAs($officer)
            ->withSession(['field_activity_child.'.$event->id => $child->id])
            ->get(route('field_officer.activities.show', $event))
            ->assertOk()
            ->assertSee('Child KFHI-960');
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

        return [$officer, $program, $event];
    }
}