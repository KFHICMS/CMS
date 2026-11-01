<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Child;
use App\Models\Event;
use App\Models\FieldActivitySubmission;
use App\Models\FollowUp;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_real_figures_not_placeholders(): void
    {
        $admin = $this->user('admin', 'active', 'Asha Admin');
        $this->user('field_officer', 'active');
        $this->user('enumerator', 'inactive');

        $active = $this->child('KFHI-D01');
        $this->child('KFHI-D02', 'inactive');

        $event = $this->event();
        Attendance::create([
            'event_id' => $event->id, 'child_id' => $active->id, 'marked_by' => $admin->id,
            'attendance_time' => now(), 'method' => 'manual', 'status' => 'present',
        ]);

        $questionnaire = Questionnaire::create(['event_id' => $event->id, 'title' => 'Survey', 'status' => 'published']);
        QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id, 'child_id' => null, 'submitted_by' => null,
            'submitted_at' => now(), 'response_uuid' => (string) Str::uuid(), 'review_status' => 'pending',
        ]);
        FollowUp::create([
            'child_id' => $active->id, 'assigned_to' => $admin->id,
            'category' => 'Health check', 'status' => 'pending', 'priority' => 'medium',
        ]);
        AuditLog::create([
            'user_id' => $admin->id, 'action' => 'questionnaire_reviewed', 'module' => 'questionnaire_response',
            'record_id' => 1, 'description' => 'Questionnaire submission review status updated.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Asha Admin')
            ->assertSee('Questionnaire submission review status updated.')
            ->assertSee('Asha Admin')
            ->assertDontSee('System Administrator account created')
            ->assertDontSee('href="#"', false)
            ->assertViewHas('stats', fn (array $stats) => $stats['children'] === 2
                && $stats['active_children'] === 1
                && $stats['users'] === 2
                && $stats['events'] === 1
                && $stats['attendance'] === 1
                && $stats['pending_reviews'] === 1
                && $stats['follow_ups'] === 1);
    }

    public function test_dashboard_quick_actions_point_to_real_pages(): void
    {
        $admin = $this->user('admin');

        $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('Review Submissions', $html);
        $this->assertStringContainsString(route('admin_submissions.index', ['review_status' => 'pending']), str_replace('&amp;', '&', $html));
    }

    public function test_only_admins_can_open_the_admin_dashboard(): void
    {
        foreach (['field_officer', 'enumerator', 'child_officer'] as $role) {
            $this->actingAs($this->user($role))->get(route('admin.dashboard'))->assertForbidden();
        }
    }

    public function test_the_admin_sidebar_is_grouped(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Records')
            ->assertSee('Programs & Events')
            ->assertSee('Review')
            ->assertSee('System');
    }

    public function test_converted_admin_pages_use_the_shared_shell(): void
    {
        [$admin, $event, $response, $followUp, $fieldSubmission] = $this->world();

        $pages = [
            [route('admin.dashboard'), 'Dashboard'],
            [route('admin_submissions.index'), 'Questionnaire Submissions'],
            [route('admin_submissions.show', $response), 'Questionnaire Submissions'],
            [route('admin_submissions.edit', $response), 'Questionnaire Submissions'],
            [route('admin_follow_ups.index'), 'Follow-ups'],
            [route('admin_follow_ups.create'), 'Follow-ups'],
            [route('admin_follow_ups.edit', $followUp), 'Follow-ups'],
            [route('field_submissions.index'), 'Field Submissions'],
            [route('field_submissions.show', $fieldSubmission), 'Field Submissions'],
            [route('events.enumerators.edit', $event), 'Events'],
        ];

        foreach ($pages as [$url, $activeTitle]) {
            $html = $this->actingAs($admin)
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

    public function test_converted_admin_pages_keep_their_key_content(): void
    {
        [$admin, , $response, , $fieldSubmission] = $this->world();

        $this->actingAs($admin)
            ->get(route('admin_submissions.index'))
            ->assertSee('Pending review')
            ->assertSee('New child')
            ->assertSee('Invitation link');

        $this->get(route('admin_submissions.show', $response))
            ->assertSee('Secret answer text')
            ->assertSee('Edit history')
            ->assertSee('New child (not linked to an existing record)');

        $this->get(route('admin_submissions.edit', $response))
            ->assertSee('Living situation')
            ->assertSee('name="edit_reason"', false);

        $this->get(route('field_submissions.show', $fieldSubmission))
            ->assertSee('Record a correction')
            ->assertSee('Correction history');
    }

    private function world(): array
    {
        $admin = $this->user('admin');
        $officer = $this->user('field_officer');
        $event = $this->event();
        $child = $this->child('KFHI-S01');

        $questionnaire = Questionnaire::create(['event_id' => $event->id, 'title' => 'Survey', 'status' => 'published']);
        $question = $questionnaire->questions()->create([
            'question_text' => 'Living situation', 'question_type' => 'text', 'is_required' => true, 'sort_order' => 1,
        ]);
        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id, 'child_id' => null, 'submitted_by' => null,
            'submitted_at' => now(), 'response_uuid' => (string) Str::uuid(),
            'respondent_name' => 'Aman Perera', 'review_status' => 'pending',
        ]);
        $response->answers()->create(['question_id' => $question->id, 'answer_text' => 'Secret answer text', 'answer_json' => null]);

        $followUp = FollowUp::create([
            'child_id' => $child->id, 'assigned_to' => $officer->id,
            'category' => 'Health check', 'status' => 'pending', 'priority' => 'medium',
        ]);
        $fieldSubmission = FieldActivitySubmission::create([
            'event_id' => $event->id, 'child_id' => $child->id, 'user_id' => $officer->id,
            'status' => 'submitted', 'attendance_status' => 'present',
            'identification_method' => 'manual', 'submitted_at' => now(),
        ]);

        return [$admin, $event, $response, $followUp, $fieldSubmission];
    }

    private function event(): Event
    {
        return Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'CARP event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ]);
    }

    private function child(string $code, string $status = 'active'): Child
    {
        return Child::create([
            'child_code' => $code,
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => $status,
        ]);
    }

    private function user(string $role, string $status = 'active', string $name = 'Test User'): User
    {
        return User::create([
            'name' => $name,
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => $status,
        ]);
    }
}