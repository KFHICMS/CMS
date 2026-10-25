<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\FollowUp;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldOfficerShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_field_officer_page_uses_the_shared_shell(): void
    {
        [$officer, $program, $event, $followUp, $submission] = $this->world();

        $pages = [
            [route('field_officer.dashboard'), 'Dashboard'],
            [route('field_officer.programs.index'), 'My Programs'],
            [route('field_officer.programs.show', $program), 'My Programs'],
            [route('field_officer.activities.index'), 'Today'],
            [route('field_officer.activities.show', $event), 'Today'],
            [route('field_officer.submissions.review', $submission), 'Today'],
            [route('field_officer.follow_ups.index'), 'Follow-ups'],
            [route('field_officer.follow_ups.show', $followUp), 'Follow-ups'],
            [route('field_officer.reports.index'), 'Reports'],
        ];

        foreach ($pages as [$url, $activeTitle]) {
            $html = $this->actingAs($officer)
                ->get($url)
                ->assertOk()
                ->assertSee('css/kfhi-ui.css', false)
                ->assertSee('Skip to main content')
                ->assertSee('Log out')
                ->getContent();

            $this->assertSame(1, substr_count($html, '<h1'), "$url should have exactly one h1");
            $this->assertSame(1, substr_count($html, '<main'), "$url should have exactly one main element");
            $this->assertStringNotContainsString('font-family: Arial', $html, "$url still has old inline styles");
            $this->assertMatchesRegularExpression(
                '/title="'.preg_quote($activeTitle, '/').'[^"]*"\s+aria-current="page"/',
                $html,
                "$url should mark '$activeTitle' as the active menu item"
            );
        }
    }

    public function test_pages_have_breadcrumbs_and_back_links(): void
    {
        [$officer, $program, $event, $followUp, $submission] = $this->world();

        $this->actingAs($officer)
            ->get(route('field_officer.activities.show', $event))
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('href="'.route('field_officer.programs.index').'"', false)
            ->assertSee('Back to Child support')
            ->assertSee('id="crumb-child"', false);

        $this->get(route('field_officer.submissions.review', $submission))
            ->assertOk()
            ->assertSee('Back to Field visit');

        $this->get(route('field_officer.follow_ups.show', $followUp))
            ->assertOk()
            ->assertSee('Back to Follow-ups');

        $this->get(route('field_officer.reports.index'))
            ->assertOk()
            ->assertSee('Back to Dashboard');
    }

    public function test_the_dashboard_shows_a_greeting_and_the_profile_in_the_top_bar(): void
    {
        [$officer] = $this->world();

        $this->actingAs($officer)
            ->get(route('field_officer.dashboard'))
            ->assertOk()
            ->assertSee('Field Officer Dashboard')
            ->assertSee('Welcome back, Kamal Perera')
            ->assertSee('Field Officer');
    }

    public function test_success_messages_are_shown_once(): void
    {
        [$officer, , , $followUp] = $this->world();

        $html = $this->actingAs($officer)
            ->withSession(['success' => 'Saved it.'])
            ->get(route('field_officer.follow_ups.show', $followUp))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Saved it.'));
    }

    private function world(): array
    {
        $officer = User::create([
            'name' => 'Kamal Perera',
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
        $child = Child::create([
            'child_code' => 'KFHI-UI1',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
        $followUp = FollowUp::create([
            'child_id' => $child->id,
            'assigned_to' => $officer->id,
            'category' => 'Health check',
            'status' => 'pending',
            'priority' => 'medium',
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

        return [$officer, $program, $event, $followUp, $submission];
    }
}