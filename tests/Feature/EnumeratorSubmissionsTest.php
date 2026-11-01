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

class EnumeratorSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_shows_only_own_submissions_and_no_answers(): void
    {
        [$me, $other, $event] = $this->world();
        $questionnaire = $this->questionnaire($event);
        $mine = $this->response($questionnaire, $me, 'KFHI-S01', 'Secret answer text');
        $this->response($questionnaire, $other, 'KFHI-S02', 'Other answer text');

        $this->actingAs($me)
            ->get(route('enumerator.submissions.index'))
            ->assertOk()
            ->assertSee('KFHI-S01')
            ->assertDontSee('KFHI-S02')
            ->assertDontSee('Secret answer text')
            ->assertSee(route('enumerator.submissions.show', $mine));
    }

    public function test_attendance_tab_is_scoped_and_filters_by_status_and_date(): void
    {
        [$me, $other, $event] = $this->world();
        $this->attendance($event, $me, 'KFHI-S10', 'present', now()->subDays(10));
        $this->attendance($event, $me, 'KFHI-S11', 'absent', now());
        $this->attendance($event, $other, 'KFHI-S12', 'present', now());

        $this->actingAs($me)
            ->get(route('enumerator.submissions.index', ['tab' => 'attendance']))
            ->assertOk()
            ->assertSee('KFHI-S10')
            ->assertSee('KFHI-S11')
            ->assertDontSee('KFHI-S12');

        $this->get(route('enumerator.submissions.index', ['tab' => 'attendance', 'status' => 'absent']))
            ->assertSee('KFHI-S11')
            ->assertDontSee('KFHI-S10');

        $this->get(route('enumerator.submissions.index', ['tab' => 'attendance', 'from' => today()->toDateString()]))
            ->assertSee('KFHI-S11')
            ->assertDontSee('KFHI-S10');

        $this->get(route('enumerator.submissions.index', ['tab' => 'attendance', 'to' => now()->subDays(5)->toDateString()]))
            ->assertSee('KFHI-S10')
            ->assertDontSee('KFHI-S11');
    }

    public function test_questionnaire_filters_by_event_and_questionnaire(): void
    {
        [$me, , $event] = $this->world();
        $secondEvent = $this->event('Second event');
        $secondEvent->enumerators()->attach($me);

        $first = $this->questionnaire($event, 'First survey');
        $second = $this->questionnaire($secondEvent, 'Second survey');
        $this->response($first, $me, 'KFHI-S20', 'a');
        $this->response($second, $me, 'KFHI-S21', 'b');

        $this->actingAs($me)
            ->get(route('enumerator.submissions.index', ['event_id' => $event->id]))
            ->assertSee('KFHI-S20')
            ->assertDontSee('KFHI-S21');

        $this->get(route('enumerator.submissions.index', ['questionnaire_id' => $second->id]))
            ->assertSee('KFHI-S21')
            ->assertDontSee('KFHI-S20');
    }

    public function test_review_page_shows_own_answers_and_blocks_other_users(): void
    {
        [$me, $other, $event] = $this->world();
        $questionnaire = $this->questionnaire($event);
        $mine = $this->response($questionnaire, $me, 'KFHI-S30', 'Secret answer text');

        $this->actingAs($me)
            ->get(route('enumerator.submissions.show', $mine))
            ->assertOk()
            ->assertSee('Secret answer text')
            ->assertSee('KFHI-S30');

        $this->actingAs($other)
            ->get(route('enumerator.submissions.show', $mine))
            ->assertNotFound();

        $this->actingAs($this->user('field_officer'))
            ->get(route('enumerator.submissions.show', $mine))
            ->assertForbidden();
    }

    public function test_history_stays_visible_after_unassignment_and_event_closure(): void
    {
        [$me, , $event] = $this->world();
        $questionnaire = $this->questionnaire($event);
        $mine = $this->response($questionnaire, $me, 'KFHI-S40', 'Kept answer');
        $this->attendance($event, $me, 'KFHI-S41', 'present', now());

        $event->enumerators()->detach($me);
        $event->update(['status' => 'completed']);

        $this->actingAs($me)
            ->get(route('enumerator.submissions.index'))
            ->assertOk()
            ->assertSee('KFHI-S40');

        $this->get(route('enumerator.submissions.show', $mine))
            ->assertOk()
            ->assertSee('Kept answer');
    }

    public function test_totals_count_only_own_records(): void
    {
        [$me, $other, $event] = $this->world();
        $questionnaire = $this->questionnaire($event);
        $this->response($questionnaire, $me, 'KFHI-S50', 'a');
        $this->response($questionnaire, $other, 'KFHI-S51', 'b');
        $this->attendance($event, $me, 'KFHI-S52', 'present', now());
        $this->attendance($event, $me, 'KFHI-S53', 'absent', now());
        $this->attendance($event, $other, 'KFHI-S54', 'present', now());

        $this->actingAs($me)
            ->get(route('enumerator.submissions.index'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['questionnaires'] === 1
                && $stats['attendance'] === 2
                && $stats['present'] === 1
                && $stats['absent'] === 1
                && $stats['events'] === 1);
    }

    public function test_guests_and_other_roles_are_blocked(): void
    {
        $this->get(route('enumerator.submissions.index'))->assertRedirect(route('login'));

        $this->actingAs($this->user('admin'))
            ->get(route('enumerator.submissions.index'))
            ->assertForbidden();
    }

    private function world(): array
    {
        $me = $this->user('enumerator');
        $other = $this->user('enumerator');
        $event = $this->event('Field event');
        $event->enumerators()->attach([$me->id, $other->id]);

        return [$me, $other, $event];
    }

    private function event(string $title): Event
    {
        return Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'title' => $title,
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ]);
    }

    private function questionnaire(Event $event, string $title = 'Home visit survey'): Questionnaire
    {
        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => $title,
            'status' => 'published',
        ]);
        $questionnaire->questions()->create([
            'question_text' => 'Living situation',
            'question_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        return $questionnaire;
    }

    private function response(Questionnaire $questionnaire, User $user, string $childCode, string $answer): QuestionnaireResponse
    {
        $child = $this->child($childCode);
        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => $child->id,
            'submitted_by' => $user->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);
        $response->answers()->create([
            'question_id' => $questionnaire->questions()->first()->id,
            'answer_text' => $answer,
            'answer_json' => null,
        ]);

        return $response;
    }

    private function attendance(Event $event, User $user, string $childCode, string $status, $time): Attendance
    {
        return Attendance::create([
            'event_id' => $event->id,
            'child_id' => $this->child($childCode)->id,
            'marked_by' => $user->id,
            'attendance_time' => $time,
            'method' => 'manual',
            'status' => $status,
        ]);
    }

    private function child(string $code): Child
    {
        return Child::create([
            'child_code' => $code,
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
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