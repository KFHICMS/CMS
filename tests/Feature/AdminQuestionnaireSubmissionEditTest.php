<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireResponse;
use App\Models\QuestionnaireResponseEdit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminQuestionnaireSubmissionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_the_saved_answers(): void
    {
        [$admin, $submission] = $this->world();

        $this->actingAs($admin)
            ->get(route('admin_submissions.edit', $submission))
            ->assertOk()
            ->assertSee('Living situation')
            ->assertSee('Original text')
            ->assertSee('name="edit_reason"', false);
    }

    public function test_admin_edits_answers_and_the_history_keeps_old_and_new_values(): void
    {
        [$admin, $submission, $q] = $this->world();

        $this->actingAs($admin)
            ->put(route('admin_submissions.update', $submission), [
                'edit_reason' => 'Corrected a typing mistake.',
                'question_'.$q['text']->id => 'Updated text',
                'question_'.$q['radio']->id => 'Option B',
                'question_'.$q['checkbox']->id => ['X', 'Y'],
            ])
            ->assertRedirect(route('admin_submissions.show', $submission));

        $this->assertSame('Updated text', $this->answer($submission, $q['text'])->answer_text);
        $this->assertSame('Option B', $this->answer($submission, $q['radio'])->answer_text);
        $this->assertSame(['X', 'Y'], $this->answerJson($this->answer($submission, $q['checkbox'])));

        $edit = QuestionnaireResponseEdit::query()->firstOrFail();
        $this->assertSame($admin->id, $edit->edited_by);
        $this->assertSame('Corrected a typing mistake.', $edit->reason);
        $this->assertCount(3, $edit->changes);

        $text = collect($edit->changes)->firstWhere('question_id', $q['text']->id);
        $this->assertSame('Original text', $text['old']);
        $this->assertSame('Updated text', $text['new']);
        $this->assertSame('Living situation', $text['question']);

        $checkbox = collect($edit->changes)->firstWhere('question_id', $q['checkbox']->id);
        $this->assertSame(['X'], $checkbox['old']);
        $this->assertSame(['X', 'Y'], $checkbox['new']);

        // The review status is not changed by an edit.
        $this->assertSame('pending', $submission->fresh()->review_status);

        $log = AuditLog::query()->where('action', 'questionnaire_answers_edited')->firstOrFail();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringNotContainsString('Updated text', (string) $log->description);

        $this->get(route('admin_submissions.show', $submission))
            ->assertOk()
            ->assertSee('Edit history')
            ->assertSee('Corrected a typing mistake.')
            ->assertSee('Original text')
            ->assertSee('Updated text');
    }

    public function test_only_changed_answers_are_recorded(): void
    {
        [$admin, $submission, $q] = $this->world();

        $this->actingAs($admin)->put(route('admin_submissions.update', $submission), $this->payload($q, [
            'question_'.$q['text']->id => 'Only this changed',
        ]));

        $edit = QuestionnaireResponseEdit::query()->firstOrFail();
        $this->assertCount(1, $edit->changes);
        $this->assertSame($q['text']->id, $edit->changes[0]['question_id']);
    }

    public function test_an_edit_with_no_changes_is_rejected(): void
    {
        [$admin, $submission, $q] = $this->world();

        $this->actingAs($admin)
            ->put(route('admin_submissions.update', $submission), $this->payload($q))
            ->assertSessionHasErrors('edit_reason');

        $this->assertSame(0, QuestionnaireResponseEdit::query()->count());
    }

    public function test_invalid_answers_and_a_missing_reason_change_nothing(): void
    {
        [$admin, $submission, $q] = $this->world();

        $this->actingAs($admin)
            ->put(route('admin_submissions.update', $submission), $this->payload($q, [
                'question_'.$q['radio']->id => 'Not an option',
            ]))
            ->assertSessionHasErrors('question_'.$q['radio']->id);

        $this->put(route('admin_submissions.update', $submission), $this->payload($q, [
            'edit_reason' => '',
            'question_'.$q['text']->id => 'Changed',
        ]))->assertSessionHasErrors('edit_reason');

        $this->assertSame('Original text', $this->answer($submission, $q['text'])->answer_text);
        $this->assertSame('Option A', $this->answer($submission, $q['radio'])->answer_text);
        $this->assertSame(0, QuestionnaireResponseEdit::query()->count());
    }

    public function test_approved_submissions_are_locked_until_set_back_to_pending(): void
    {
        [$admin, $submission, $q] = $this->world();
        $submission->update(['review_status' => 'approved']);

        $this->actingAs($admin)
            ->get(route('admin_submissions.edit', $submission))
            ->assertRedirect(route('admin_submissions.show', $submission))
            ->assertSessionHas('error');

        $this->put(route('admin_submissions.update', $submission), $this->payload($q, [
            'question_'.$q['text']->id => 'Sneaky change',
        ]))->assertRedirect(route('admin_submissions.show', $submission));

        $this->assertSame('Original text', $this->answer($submission, $q['text'])->answer_text);
        $this->assertSame(0, QuestionnaireResponseEdit::query()->count());

        $this->get(route('admin_submissions.show', $submission))
            ->assertOk()
            ->assertSee('locked while the submission is approved');

        // Setting it back to pending unlocks editing.
        $this->patch(route('admin_submissions.review', $submission), ['review_status' => 'pending']);
        $this->get(route('admin_submissions.edit', $submission))->assertOk();
        $this->put(route('admin_submissions.update', $submission), $this->payload($q, [
            'question_'.$q['text']->id => 'Now allowed',
        ]))->assertRedirect(route('admin_submissions.show', $submission));

        $this->assertSame('Now allowed', $this->answer($submission, $q['text'])->answer_text);
    }

    public function test_rejected_submissions_can_be_edited(): void
    {
        [$admin, $submission, $q] = $this->world();
        $submission->update(['review_status' => 'rejected']);

        $this->actingAs($admin)
            ->put(route('admin_submissions.update', $submission), $this->payload($q, [
                'question_'.$q['text']->id => 'Fixed after rejection',
            ]))
            ->assertRedirect(route('admin_submissions.show', $submission));

        $this->assertSame('Fixed after rejection', $this->answer($submission, $q['text'])->answer_text);
        $this->assertSame('rejected', $submission->fresh()->review_status);
    }

    public function test_responses_from_logged_in_users_are_locked_by_default(): void
    {
        [$admin, , $q, $questionnaire] = $this->world();
        $enumerator = $this->user('enumerator');

        $loggedIn = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => null,
            'submitted_by' => $enumerator->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $this->assertSame('approved', $loggedIn->fresh()->review_status);

        $this->actingAs($admin)
            ->get(route('admin_submissions.edit', $loggedIn))
            ->assertRedirect(route('admin_submissions.show', $loggedIn));
    }

    public function test_only_admins_can_edit(): void
    {
        [, $submission, $q] = $this->world();

        $this->get(route('admin_submissions.edit', $submission))->assertRedirect(route('login'));

        foreach (['field_officer', 'enumerator', 'child_officer'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('admin_submissions.edit', $submission))->assertForbidden();
            $this->actingAs($user)
                ->put(route('admin_submissions.update', $submission), $this->payload($q, [
                    'question_'.$q['text']->id => 'Hacked',
                ]))
                ->assertForbidden();
        }

        $this->assertSame('Original text', $this->answer($submission, $q['text'])->answer_text);
        $this->assertSame(0, QuestionnaireResponseEdit::query()->count());
    }

    private function payload(array $q, array $overrides = []): array
    {
        return array_merge([
            'edit_reason' => 'Checking the edit rules.',
            'question_'.$q['text']->id => 'Original text',
            'question_'.$q['radio']->id => 'Option A',
            'question_'.$q['checkbox']->id => ['X'],
        ], $overrides);
    }

    private function answer(QuestionnaireResponse $submission, $question): QuestionnaireAnswer
    {
        return QuestionnaireAnswer::query()
            ->where('response_id', $submission->id)
            ->where('question_id', $question->id)
            ->firstOrFail();
    }

    private function answerJson(QuestionnaireAnswer $answer): array
    {
        $value = $answer->answer_json;

        return is_string($value) ? json_decode($value, true) : $value;
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

        $make = fn (string $type, string $text, int $order, array $options = []) => tap(
            $questionnaire->questions()->create([
                'question_text' => $text,
                'question_type' => $type,
                'is_required' => true,
                'sort_order' => $order,
            ]),
            function ($question) use ($options) {
                foreach ($options as $index => $label) {
                    $question->options()->create(['option_label' => $label, 'option_value' => null, 'sort_order' => $index]);
                }
            }
        );

        $q = [
            'text' => $make('text', 'Living situation', 1),
            'radio' => $make('radio', 'Preferred option', 2, ['Option A', 'Option B']),
            'checkbox' => $make('checkbox', 'Services used', 3, ['X', 'Y', 'Z']),
        ];

        $submission = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => null,
            'submitted_by' => null,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
            'respondent_name' => 'Aman Perera',
            'review_status' => 'pending',
        ]);
        $submission->answers()->create(['question_id' => $q['text']->id, 'answer_text' => 'Original text', 'answer_json' => null]);
        $submission->answers()->create(['question_id' => $q['radio']->id, 'answer_text' => 'Option A', 'answer_json' => null]);
        $submission->answers()->create(['question_id' => $q['checkbox']->id, 'answer_text' => null, 'answer_json' => ['X']]);

        return [$admin, $submission, $q, $questionnaire];
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