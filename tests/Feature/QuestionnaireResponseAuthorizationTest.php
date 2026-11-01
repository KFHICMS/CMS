<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Event;
use App\Models\Program;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
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

        // Enumerators are allowed by role but must be assigned to the event.
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

        $otherOfficer = $this->createUser('field_officer');
        $this->actingAs($otherOfficer)
            ->get(route('questionnaires.responses.create', [$questionnaire, $child]))
            ->assertNotFound();

        $this->get(route('questionnaires.responses.success', [$questionnaire, $child]))
            ->assertNotFound();
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
