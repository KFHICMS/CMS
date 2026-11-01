<?php

namespace Tests\Feature;

use App\Http\Controllers\QuestionnaireResponseController;
use App\Models\AuditLog;
use App\Models\Child;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnumeratorQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_enumerator_identifies_the_child_then_submits_valid_answers(): void
    {
        [$enumerator, $event, $questionnaire, $q] = $this->setUpForm();
        $child = $this->child('KFHI-Q01');

        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), [
                'child_code' => 'KFHI-Q01',
            ])
            ->assertOk()
            ->assertJsonPath('child.child_code', 'KFHI-Q01')
            ->assertJsonMissingPath('child.id');

        $form = route('questionnaires.responses.create', [$questionnaire, $child]);
        $this->get($form)->assertOk()->assertSee('KFHI-Q01');

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]), $this->validAnswers($q))
            ->assertRedirect(route('enumerator.events.questionnaires.show', [$event, $questionnaire]))
            ->assertSessionHas('success');

        $response = QuestionnaireResponse::query()->firstOrFail();
        $this->assertSame($enumerator->id, $response->submitted_by);
        $this->assertSame($child->id, $response->child_id);

        $this->assertDatabaseHas('questionnaire_answers', [
            'question_id' => $q['radio']->id,
            'answer_text' => 'Option A',
        ]);

        $checkbox = QuestionnaireAnswer::query()->where('question_id', $q['checkbox']->id)->firstOrFail()->answer_json;
        $checkbox = is_string($checkbox) ? json_decode($checkbox, true) : $checkbox;
        $this->assertSame(['X', 'Y'], $checkbox);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $enumerator->id,
            'action' => 'questionnaire_submitted',
        ]);

        $this->get(route('questionnaires.responses.success', [$questionnaire, $child]))->assertOk();
    }

    public function test_form_cannot_be_opened_or_submitted_without_identifying_the_child(): void
    {
        [$enumerator, $event, $questionnaire, $q] = $this->setUpForm();
        $child = $this->child('KFHI-Q02');
        $other = $this->child('KFHI-Q03');
        $identifyPage = route('enumerator.events.questionnaires.show', [$event, $questionnaire]);

        $this->actingAs($enumerator)
            ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertRedirect($identifyPage);

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]), $this->validAnswers($q))
            ->assertRedirect($identifyPage);

        // Identifying one child does not unlock another child's form.
        $this->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), [
            'child_code' => 'KFHI-Q02',
        ])->assertOk();

        $this->get(route('questionnaires.responses.create', [$questionnaire, $other]))
            ->assertRedirect($identifyPage);

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_invalid_answers_are_rejected_and_nothing_is_saved(): void
    {
        [$enumerator, $event, $questionnaire, $q] = $this->setUpForm();
        $child = $this->child('KFHI-Q04');
        $this->identify($enumerator, $event, $questionnaire, 'KFHI-Q04');

        $bad = $this->validAnswers($q);
        $bad['question_'.$q['radio']->id] = 'Not an option';
        $bad['question_'.$q['checkbox']->id] = ['X', 'Nope'];
        $bad['question_'.$q['number']->id] = 'abc';
        $bad['question_'.$q['date']->id] = '2026-02-31';
        $bad['question_'.$q['rating']->id] = '9';
        $bad['question_'.$q['yes_no']->id] = 'Maybe';
        unset($bad['question_'.$q['text']->id]);

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]), $bad)
            ->assertSessionHasErrors([
                'question_'.$q['text']->id,
                'question_'.$q['radio']->id,
                'question_'.$q['checkbox']->id.'.1',
                'question_'.$q['number']->id,
                'question_'.$q['date']->id,
                'question_'.$q['rating']->id,
                'question_'.$q['yes_no']->id,
            ]);

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_duplicate_submissions_are_rejected(): void
    {
        [$enumerator, $event, $questionnaire, $q] = $this->setUpForm();
        $child = $this->child('KFHI-Q05');
        QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => $child->id,
            'submitted_by' => $enumerator->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), [
                'child_code' => 'KFHI-Q05',
            ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'This child has already answered this questionnaire.');

        // Direct POST with the identification already in the session.
        $this->withSession([QuestionnaireResponseController::IDENTIFIED_SESSION.$questionnaire->id => $child->id])
            ->post(route('questionnaires.responses.store', [$questionnaire, $child]), $this->validAnswers($q))
            ->assertRedirect(route('enumerator.events.questionnaires.show', [$event, $questionnaire]))
            ->assertSessionHas('error', 'This child has already answered this questionnaire.');

        $this->assertSame(1, QuestionnaireResponse::query()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'questionnaire_duplicate']);
    }

    public function test_access_rules_are_enforced_on_the_backend(): void
    {
        [$enumerator, $event, $questionnaire] = $this->setUpForm();
        $child = $this->child('KFHI-Q06');
        $stranger = $this->user('enumerator');
        $inactive = $this->user('enumerator', 'inactive');
        $event->enumerators()->attach($inactive);

        // Not assigned to the event.
        $this->actingAs($stranger)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => 'KFHI-Q06'])
            ->assertNotFound();
        $this->get(route('questionnaires.responses.create', [$questionnaire, $child]))->assertNotFound();

        // Deactivated account.
        $this->actingAs($inactive)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => 'KFHI-Q06'])
            ->assertForbidden();

        // Draft questionnaires are not available.
        $draft = Questionnaire::create(['event_id' => $event->id, 'title' => 'Draft', 'status' => 'draft']);
        $this->actingAs($enumerator)
            ->get(route('enumerator.events.questionnaires.show', [$event, $draft]))
            ->assertNotFound();

        // Questionnaire switch off.
        $event->update(['enable_questionnaires' => false]);
        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => 'KFHI-Q06'])
            ->assertForbidden();
    }

    public function test_closed_or_not_started_events_do_not_accept_responses(): void
    {
        foreach ([['status' => 'cancelled'], ['status' => 'completed'], ['event_date' => today()->addDay()]] as $overrides) {
            [$enumerator, $event, $questionnaire, $q] = $this->setUpForm($overrides);
            $child = $this->child('KFHI-R'.uniqid());

            $this->actingAs($enumerator)
                ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => 'ANY'])
                ->assertStatus(409);

            $this->withSession([QuestionnaireResponseController::IDENTIFIED_SESSION.$questionnaire->id => $child->id])
                ->post(route('questionnaires.responses.store', [$questionnaire, $child]), $this->validAnswers($q))
                ->assertRedirect(route('enumerator.events.show', $event));
        }

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_closed_questionnaires_reject_new_responses(): void
    {
        [$enumerator, $event, $questionnaire] = $this->setUpForm();
        $this->child('KFHI-Q07');
        $questionnaire->update(['status' => 'closed']);

        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => 'KFHI-Q07'])
            ->assertStatus(409);
    }

    public function test_success_page_is_only_available_to_the_submitter(): void
    {
        [$enumerator, $event, $questionnaire] = $this->setUpForm();
        $child = $this->child('KFHI-Q08');
        $second = $this->user('enumerator');
        $event->enumerators()->attach($second);
        QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'child_id' => $child->id,
            'submitted_by' => $enumerator->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $url = route('questionnaires.responses.success', [$questionnaire, $child]);
        $this->actingAs($second)->get($url)->assertNotFound();
        $this->actingAs($enumerator)->get($url)->assertOk();
    }

    private function identify(User $enumerator, Event $event, Questionnaire $questionnaire, string $code): void
    {
        $this->actingAs($enumerator)
            ->postJson(route('enumerator.events.questionnaires.identify', [$event, $questionnaire]), ['child_code' => $code])
            ->assertOk();
    }

    private function validAnswers(array $q): array
    {
        return [
            'question_'.$q['text']->id => 'Lives with grandparents',
            'question_'.$q['radio']->id => 'Option A',
            'question_'.$q['checkbox']->id => ['X', 'Y'],
            'question_'.$q['number']->id => '12.5',
            'question_'.$q['date']->id => '2026-10-01',
            'question_'.$q['rating']->id => '4',
            'question_'.$q['yes_no']->id => 'Yes',
        ];
    }

    private function setUpForm(array $eventOverrides = []): array
    {
        $enumerator = $this->user('enumerator');
        $event = Event::create(array_merge([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'Field event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ], $eventOverrides));
        $event->enumerators()->attach($enumerator);

        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'Home visit survey',
            'status' => 'published',
        ]);

        $make = fn (string $type, string $text, array $options = []) => tap(
            $questionnaire->questions()->create([
                'question_text' => $text,
                'question_type' => $type,
                'is_required' => true,
                'sort_order' => 1,
            ]),
            function ($question) use ($options) {
                foreach ($options as $index => $label) {
                    $question->options()->create(['option_label' => $label, 'option_value' => null, 'sort_order' => $index]);
                }
            }
        );

        $q = [
            'text' => $make('text', 'Living situation'),
            'radio' => $make('radio', 'Preferred option', ['Option A', 'Option B']),
            'checkbox' => $make('checkbox', 'Services used', ['X', 'Y', 'Z']),
            'number' => $make('number', 'Household size'),
            'date' => $make('date', 'Last visit'),
            'rating' => $make('rating', 'Satisfaction'),
            'yes_no' => $make('yes_no', 'Attends school'),
        ];

        return [$enumerator, $event, $questionnaire, $q];
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

    public function test_enumerator_can_continue_with_the_next_child_after_submitting(): void
    {
        [$enumerator, $event, $questionnaire, $q] = $this->setUpForm();
        $first = $this->child('KFHI-N01');
        $second = $this->child('KFHI-N02');

        $this->identify($enumerator, $event, $questionnaire, 'KFHI-N01');
        $this->post(route('questionnaires.responses.store', [$questionnaire, $first]), $this->validAnswers($q))
            ->assertRedirect(route('enumerator.events.questionnaires.show', [$event, $questionnaire]));

        // The first child's identification is cleared, so the next child must be identified.
        $this->get(route('questionnaires.responses.create', [$questionnaire, $second]))
            ->assertRedirect(route('enumerator.events.questionnaires.show', [$event, $questionnaire]));

        $this->identify($enumerator, $event, $questionnaire, 'KFHI-N02');
        $this->post(route('questionnaires.responses.store', [$questionnaire, $second]), $this->validAnswers($q))
            ->assertRedirect(route('enumerator.events.questionnaires.show', [$event, $questionnaire]));

        $this->assertSame(2, QuestionnaireResponse::query()->count());
    }
}