<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\Program;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireResponseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_questionnaire_responses_require_an_authenticated_allowed_role(): void
    {
        [$questionnaire, $child] = $this->publishedQuestionnaire();
        $route = route('questionnaires.responses.create', [$questionnaire, $child]);

        $this->get($route)
            ->assertRedirect(route('login'));

        $this->actingAs($this->createUser('enumerator'))
            ->get($route)
            ->assertNotFound();
    }

    public function test_field_officers_can_only_respond_for_assigned_programs(): void
    {
        $officer = $this->createUser('field_officer');
        [$questionnaire, $child] = $this->publishedQuestionnaire($officer);

        $this->actingAs($officer)
            ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertOk()
            ->assertSee($child->child_code);

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]))
            ->assertRedirect(route('questionnaires.responses.success', [$questionnaire, $child]));

        $response = QuestionnaireResponse::query()->firstOrFail();
        $this->assertSame($officer->id, $response->submitted_by);

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]))
            ->assertRedirect();
        $this->assertSame(1, QuestionnaireResponse::query()->count());

        $otherOfficer = $this->createUser('field_officer');
        $this->actingAs($otherOfficer)
            ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertNotFound();

        $this->get(route('questionnaires.responses.success', [$questionnaire, $child]))
            ->assertNotFound();
    }

    public function test_assigned_enumerators_use_the_shared_questionnaire_response_flow(): void
    {
        $enumerator = $this->createUser('enumerator');
        [$questionnaire, $child] = $this->publishedQuestionnaire();
        $questionnaire->event->enumerators()->attach($enumerator);

        $this->actingAs($enumerator)
            ->get(route('enumerator.events.show', $questionnaire->event))
            ->assertOk()
            ->assertSee(route('enumerator.events.questionnaires.start', [$questionnaire->event, $questionnaire]));

        $this->post(route('enumerator.events.questionnaires.start', [$questionnaire->event, $questionnaire]), [
            'child_code' => $child->child_code,
        ])
            ->assertRedirect(route('questionnaires.responses.create', [$questionnaire, $child]));

        $this->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertOk()
            ->assertSee($questionnaire->title);

        $this->actingAs($this->createUser('enumerator'))
            ->post(route('enumerator.events.questionnaires.start', [$questionnaire->event, $questionnaire]), [
                'child_code' => $child->child_code,
            ])
            ->assertNotFound();
    }

    public function test_questionnaire_start_rechecks_switch_and_published_status(): void
    {
        $enumerator = $this->createUser('enumerator');
        [$questionnaire, $child] = $this->publishedQuestionnaire();
        $questionnaire->event->enumerators()->attach($enumerator);
        $startRoute = route('enumerator.events.questionnaires.start', [$questionnaire->event, $questionnaire]);

        $this->actingAs($enumerator)
            ->post($startRoute, ['child_code' => $child->child_code])
            ->assertRedirect(route('questionnaires.responses.create', [$questionnaire, $child]));

        $questionnaire->event->update(['enable_questionnaires' => false]);
        $this->post($startRoute, ['child_code' => $child->child_code])->assertNotFound();
        $this->get(route('questionnaires.responses.create', [$questionnaire, $child]))->assertNotFound();

        $questionnaire->event->update(['enable_questionnaires' => true]);
        $questionnaire->update(['status' => 'draft']);
        $this->post($startRoute, ['child_code' => $child->child_code])->assertNotFound();
        $this->get(route('questionnaires.responses.create', [$questionnaire, $child]))->assertNotFound();
    }

    public function test_answer_values_options_ranges_and_dates_are_validated(): void
    {
        $enumerator = $this->createUser('enumerator');
        [$questionnaire, $child] = $this->publishedQuestionnaire();
        $questionnaire->event->enumerators()->attach($enumerator);

        $choice = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Choose one',
            'question_type' => 'select',
            'is_required' => true,
        ]);
        QuestionOption::create([
            'question_id' => $choice->id,
            'option_label' => 'Approved',
        ]);
        $number = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'How many?',
            'question_type' => 'number',
            'is_required' => true,
            'settings' => ['min' => 1, 'max' => 5],
        ]);
        $date = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'When?',
            'question_type' => 'date',
            'is_required' => true,
            'settings' => ['min_date' => '2026-01-01', 'max_date' => '2026-12-31'],
        ]);

        $this->actingAs($enumerator)
            ->from(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->post(route('questionnaires.responses.store', [$questionnaire, $child]), [
                'question_'.$choice->id => 'Forged choice',
                'question_'.$number->id => '10',
                'question_'.$date->id => '2027-01-01',
            ])
            ->assertRedirect(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertSessionHasErrors([
                'question_'.$choice->id,
                'question_'.$number->id,
                'question_'.$date->id,
            ]);

        $this->assertSame(0, QuestionnaireResponse::query()->count());

        $this->post(route('questionnaires.responses.store', [$questionnaire, $child]), [
            'question_'.$choice->id => 'Approved',
            'question_'.$number->id => '5',
            'question_'.$date->id => '2026-10-20',
        ])
            ->assertRedirect(route('questionnaires.responses.success', [$questionnaire, $child]));

        $this->assertSame(3, QuestionnaireResponse::query()->firstOrFail()->answers()->count());
    }

    public function test_admin_and_child_officer_can_access_enabled_published_questionnaires(): void
    {
        [$questionnaire, $child] = $this->publishedQuestionnaire();

        foreach (['admin', 'child_officer'] as $role) {
            $this->actingAs($this->createUser($role))
                ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
                ->assertOk();
        }
    }

    public function test_disabled_or_unpublished_questionnaires_are_not_accessible(): void
    {
        [$questionnaire, $child] = $this->publishedQuestionnaire();
        $questionnaire->event->update(['enable_questionnaires' => false]);

        $this->actingAs($this->createUser('admin'))
            ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertNotFound();

        $questionnaire->event->update(['enable_questionnaires' => true]);
        $questionnaire->update(['status' => 'draft']);

        $this->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertNotFound();
    }

    private function publishedQuestionnaire(?User $officer = null): array
    {
        $program = Program::create([
            'name' => 'Questionnaire program',
            'status' => 'active',
        ]);

        if ($officer) {
            $program->officers()->attach($officer);
        }

        $event = Event::create([
            'event_code' => 'QST-'.uniqid(),
            'program_id' => $program->id,
            'title' => 'Questionnaire event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_questionnaires' => true,
        ]);

        $questionnaire = Questionnaire::create([
            'event_id' => $event->id,
            'title' => 'Child wellbeing',
            'status' => 'published',
        ]);

        $child = Child::create([
            'child_code' => 'QST-CHILD-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Child',
            'status' => 'active',
        ]);

        return [$questionnaire, $child];
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
