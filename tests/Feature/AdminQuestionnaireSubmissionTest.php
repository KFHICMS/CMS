<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Child;
use App\Models\EnumeratorInvitation;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminQuestionnaireSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_public_and_logged_in_submissions_with_filters(): void
    {
        [$admin, $questionnaire, $invitation] = $this->world();
        $enumerator = $this->user('enumerator');
        $child = $this->child('KFHI-A01');

        $this->response($questionnaire, ['invitation_id' => $invitation->id, 'respondent_name' => 'Aman Perera', 'review_status' => 'pending']);
        $this->response($questionnaire, ['child_id' => $child->id, 'submitted_by' => $enumerator->id]);

        $this->actingAs($admin)
            ->get(route('admin_submissions.index'))
            ->assertOk()
            ->assertSee('Aman Perera')
            ->assertSee('New child')
            ->assertSee('KFHI-A01')
            ->assertSee('Invitation link')
            ->assertSee('Enumerator');

        $this->get(route('admin_submissions.index', ['source' => 'invitation']))
            ->assertSee('Aman Perera')
            ->assertDontSee('KFHI-A01');

        $this->get(route('admin_submissions.index', ['review_status' => 'approved']))
            ->assertSee('KFHI-A01')
            ->assertDontSee('Aman Perera');
    }

    public function test_show_page_displays_answers_and_linked_or_new_child(): void
    {
        [$admin, $questionnaire, $invitation] = $this->world();
        $child = $this->child('KFHI-A02');

        $linked = $this->response($questionnaire, ['child_id' => $child->id, 'invitation_id' => $invitation->id, 'respondent_name' => 'Aman Perera']);
        $unlinked = $this->response($questionnaire, ['invitation_id' => $invitation->id, 'respondent_name' => 'Nimal Silva']);

        $this->actingAs($admin)
            ->get(route('admin_submissions.show', $linked))
            ->assertOk()
            ->assertSee('Secret answer text')
            ->assertSee('KFHI-A02');

        $this->get(route('admin_submissions.show', $unlinked))
            ->assertOk()
            ->assertSee('New child (not linked to an existing record)');
    }

    public function test_admin_can_review_a_submission_and_it_is_audited(): void
    {
        [$admin, $questionnaire, $invitation] = $this->world();
        $submission = $this->response($questionnaire, ['invitation_id' => $invitation->id, 'review_status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin_submissions.review', $submission), [
                'review_status' => 'rejected',
                'review_note' => 'Photo is blurry, please recollect.',
            ])
            ->assertRedirect(route('admin_submissions.show', $submission));

        $fresh = $submission->fresh();
        $this->assertSame('rejected', $fresh->review_status);
        $this->assertSame($admin->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);
        $this->assertSame('Photo is blurry, please recollect.', $fresh->review_note);

        $log = AuditLog::query()->where('action', 'questionnaire_reviewed')->firstOrFail();
        $this->assertStringNotContainsString('blurry', (string) $log->description);

        // Setting it back to pending clears the reviewer.
        $this->patch(route('admin_submissions.review', $submission), ['review_status' => 'pending']);
        $this->assertNull($submission->fresh()->reviewed_by);
        $this->assertNull($submission->fresh()->reviewed_at);
    }

    public function test_invalid_review_status_is_rejected(): void
    {
        [$admin, $questionnaire] = $this->world();
        $submission = $this->response($questionnaire, ['review_status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin_submissions.review', $submission), ['review_status' => 'banana'])
            ->assertSessionHasErrors('review_status');

        $this->assertSame('pending', $submission->fresh()->review_status);
    }

    public function test_only_admins_can_open_the_pages(): void
    {
        [, $questionnaire] = $this->world();
        $submission = $this->response($questionnaire);

        $this->get(route('admin_submissions.index'))->assertRedirect(route('login'));

        foreach (['field_officer', 'enumerator', 'child_officer'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('admin_submissions.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin_submissions.show', $submission))->assertForbidden();
            $this->actingAs($user)
                ->patch(route('admin_submissions.review', $submission), ['review_status' => 'approved'])
                ->assertForbidden();
        }

        $this->assertSame('approved', $submission->fresh()->review_status);
    }

    private function response(Questionnaire $questionnaire, array $overrides = []): QuestionnaireResponse
    {
        $response = QuestionnaireResponse::create(array_merge([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => null,
            'submitted_by' => null,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ], $overrides));

        $response->answers()->create([
            'question_id' => $questionnaire->questions()->first()->id,
            'answer_text' => 'Secret answer text',
            'answer_json' => null,
        ]);

        return $response;
    }

    private function world(): array
    {
        $admin = $this->user('admin');
        $event = Event::create([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'CARP event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ]);
        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'CARP survey',
            'status' => 'published',
        ]);
        $questionnaire->questions()->create([
            'question_text' => 'Living situation',
            'question_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $invitation = EnumeratorInvitation::create([
            'event_id' => $event->id,
            'questionnaire_id' => $questionnaire->id,
            'token_hash' => hash('sha256', Str::random(43)),
            'token_hint' => 'abcd',
            'max_submissions' => 100,
            'expires_at' => now()->addDays(7),
        ]);

        return [$admin, $questionnaire, $invitation];
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