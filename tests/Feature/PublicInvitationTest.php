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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_link_shows_the_form_without_any_cms_data(): void
    {
        $d = $this->setUpData();
        Child::create(['child_code' => 'KFHI-PUB1', 'first_name' => 'Hidden', 'last_name' => 'Child', 'status' => 'active']);

        $this->get(route('public.invitations.show', $d['token']))
            ->assertOk()
            ->assertSee('CARP survey')
            ->assertSee('Your name')
            ->assertSee('Living situation')
            ->assertDontSee('KFHI-PUB1')
            ->assertDontSee('Hidden')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertHeader('Referrer-Policy', 'no-referrer');
    }

    public function test_invalid_and_malformed_tokens_are_not_found(): void
    {
        $this->setUpData();

        $this->get(route('public.invitations.show', EnumeratorInvitation::generateToken()))->assertNotFound();
        $this->get('/join/too-short')->assertNotFound();
        $this->post(route('public.invitations.store', EnumeratorInvitation::generateToken()), [])->assertNotFound();
    }

    public function test_expired_and_revoked_links_do_not_open_or_accept_submissions(): void
    {
        $expired = $this->setUpData(['expires_at' => now()->subMinute()]);
        $revoked = $this->setUpData(['revoked_at' => now()]);

        $this->get(route('public.invitations.show', $expired['token']))
            ->assertStatus(410)
            ->assertSee('This link has expired');
        $this->get(route('public.invitations.show', $revoked['token']))
            ->assertStatus(410)
            ->assertSee('no longer active');

        $this->post(route('public.invitations.store', $expired['token']), $this->payload($expired))->assertStatus(410);
        $this->post(route('public.invitations.store', $revoked['token']), $this->payload($revoked))->assertStatus(410);

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_questionnaire_and_event_settings_cannot_be_bypassed(): void
    {
        $cases = [
            'draft questionnaire' => [403, ['questionnaire' => ['status' => 'draft']]],
            'closed questionnaire' => [403, ['questionnaire' => ['status' => 'closed']]],
            'questionnaires disabled' => [403, ['event' => ['enable_questionnaires' => false]]],
            'completed event' => [409, ['event' => ['status' => 'completed']]],
            'cancelled event' => [409, ['event' => ['status' => 'cancelled']]],
            'event not started' => [409, ['event' => ['event_date' => today()->addDay()]]],
        ];

        foreach ($cases as $label => [$status, $overrides]) {
            $d = $this->setUpData([], $overrides['event'] ?? [], $overrides['questionnaire'] ?? []);

            $this->get(route('public.invitations.show', $d['token']))->assertStatus($status);
            $this->post(route('public.invitations.store', $d['token']), $this->payload($d))->assertStatus($status);
        }

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_valid_submission_is_saved_as_a_pending_public_response(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d);

        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertRedirect(route('public.invitations.thanks', $d['token']));

        $response = QuestionnaireResponse::query()->firstOrFail();
        $this->assertNull($response->child_id);
        $this->assertNull($response->submitted_by);
        $this->assertSame($d['invitation']->id, $response->invitation_id);
        $this->assertSame($d['questionnaire']->id, $response->questionnaire_id);
        $this->assertSame('Aman Perera', $response->respondent_name);
        $this->assertSame('pending', $response->review_status);
        $this->assertSame($payload['submission_key'], $response->idempotency_key);

        $this->assertDatabaseHas('questionnaire_answers', [
            'response_id' => $response->id,
            'question_id' => $d['q']['radio']->id,
            'answer_text' => 'Option A',
        ]);
        $this->assertSame(7, $response->answers()->count());

        $log = AuditLog::query()->where('action', 'public_submission_received')->firstOrFail();
        $this->assertNull($log->user_id);
        $this->assertSame($response->id, $log->record_id);
        $this->assertStringNotContainsString($d['token'], (string) $log->description);
        $this->assertStringNotContainsString('Aman', (string) $log->description);

        $this->get(route('public.invitations.thanks', $d['token']))
            ->assertOk()
            ->assertSee('Thank you');
    }

    public function test_invalid_answers_and_missing_name_are_rejected(): void
    {
        $d = $this->setUpData();
        $q = $d['q'];

        $bad = [
            'submission_key' => (string) Str::uuid(),
            'question_'.$q['radio']->id => 'Not an option',
            'question_'.$q['checkbox']->id => ['X', 'Nope'],
            'question_'.$q['number']->id => 'abc',
            'question_'.$q['date']->id => '2026-02-31',
            'question_'.$q['rating']->id => '9',
            'question_'.$q['yes_no']->id => 'Maybe',
        ];

        $this->post(route('public.invitations.store', $d['token']), $bad)
            ->assertSessionHasErrors([
                'respondent_name',
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

    public function test_retrying_with_the_same_key_does_not_create_a_duplicate(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d);

        $this->post(route('public.invitations.store', $d['token']), $payload);
        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertRedirect(route('public.invitations.thanks', $d['token']));

        $this->assertSame(1, QuestionnaireResponse::query()->count());
    }

    public function test_submission_limit_is_enforced_and_children_are_not_required(): void
    {
        $d = $this->setUpData(['max_submissions' => 2]);
        $url = route('public.invitations.store', $d['token']);

        $this->post($url, $this->payload($d))->assertRedirect();
        $this->post($url, $this->payload($d))->assertRedirect();
        $this->post($url, $this->payload($d))->assertStatus(409);

        $this->assertSame(2, QuestionnaireResponse::query()->count());
        $this->assertSame(2, QuestionnaireResponse::query()->whereNull('child_id')->count());
        $this->get(route('public.invitations.show', $d['token']))
            ->assertStatus(409)
            ->assertSee('submission limit');
    }

    public function test_revoking_or_expiring_after_the_form_was_opened_blocks_the_submit(): void
    {
        $d = $this->setUpData();
        $this->get(route('public.invitations.show', $d['token']))->assertOk();

        $d['invitation']->update(['revoked_at' => now()]);
        $this->post(route('public.invitations.store', $d['token']), $this->payload($d))->assertStatus(410);

        $e = $this->setUpData();
        $this->get(route('public.invitations.show', $e['token']))->assertOk();
        $e['invitation']->update(['expires_at' => now()->subMinute()]);
        $this->post(route('public.invitations.store', $e['token']), $this->payload($e))->assertStatus(410);

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_the_link_grants_no_access_to_any_cms_area(): void
    {
        $d = $this->setUpData();
        $child = Child::create(['child_code' => 'KFHI-PUB2', 'first_name' => 'Hidden', 'last_name' => 'Child', 'status' => 'active']);

        $this->get(route('public.invitations.show', $d['token']))->assertOk();

        $this->get(route('enumerator.dashboard'))->assertRedirect(route('login'));
        $this->get(route('field_officer.dashboard'))->assertRedirect(route('login'));
        $this->get(route('events.index'))->assertRedirect(route('login'));
        $this->get(route('questionnaires.responses.create', [$d['questionnaire'], $child]))
            ->assertRedirect(route('login'));
    }

    public function test_public_submissions_do_not_affect_the_enumerator_dashboard(): void
    {
        $d = $this->setUpData();
        $enumerator = $this->user('enumerator');
        $d['event']->enumerators()->attach($enumerator);

        $this->post(route('public.invitations.store', $d['token']), $this->payload($d))->assertRedirect();

        $this->actingAs($enumerator)
            ->get(route('enumerator.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['questionnaires'] === 0);

        $this->get(route('enumerator.submissions.index'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['questionnaires'] === 0);
    }

    public function test_existing_flows_keep_the_default_review_status(): void
    {
        $d = $this->setUpData();
        $child = Child::create(['child_code' => 'KFHI-PUB3', 'first_name' => 'Test', 'last_name' => 'Child', 'status' => 'active']);

        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $d['questionnaire']->id,
            'child_id' => $child->id,
            'submitted_by' => $this->user('admin')->id,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $this->assertSame('approved', $response->fresh()->review_status);
    }

    public function test_submit_endpoint_is_rate_limited(): void
    {
        $d = $this->setUpData();
        $url = route('public.invitations.store', $d['token']);

        for ($i = 0; $i < 30; $i++) {
            $this->post($url, [])->assertStatus(302);
        }

        $this->post($url, [])->assertStatus(429);
    }

    private function payload(array $d): array
    {
        $q = $d['q'];

        return [
            'respondent_name' => 'Aman Perera',
            'submission_key' => (string) Str::uuid(),
            'question_'.$q['text']->id => 'Lives with grandparents',
            'question_'.$q['radio']->id => 'Option A',
            'question_'.$q['checkbox']->id => ['X', 'Y'],
            'question_'.$q['number']->id => '12.5',
            'question_'.$q['date']->id => '2026-10-01',
            'question_'.$q['rating']->id => '4',
            'question_'.$q['yes_no']->id => 'Yes',
            'child_mode' => 'new',
            'photo' => UploadedFile::fake()->image('child.jpg', 600, 400),
        ];
    }

    private function setUpData(array $invitation = [], array $event = [], array $questionnaire = []): array
    {
        $eventModel = Event::create(array_merge([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'CARP event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ], $event));

        $questionnaireModel = Questionnaire::create(array_merge([
            'event_id' => $eventModel->id,
            'title' => 'CARP survey',
            'status' => 'published',
        ], $questionnaire));

        $make = fn (string $type, string $text, int $order, array $options = []) => tap(
            $questionnaireModel->questions()->create([
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
            'number' => $make('number', 'Household size', 4),
            'date' => $make('date', 'Last visit', 5),
            'rating' => $make('rating', 'Satisfaction', 6),
            'yes_no' => $make('yes_no', 'Attends school', 7),
        ];

        $token = EnumeratorInvitation::generateToken();
        $invitationModel = EnumeratorInvitation::create(array_merge([
            'event_id' => $eventModel->id,
            'questionnaire_id' => $questionnaireModel->id,
            'token_hash' => EnumeratorInvitation::hashToken($token),
            'token_hint' => substr($token, -4),
            'max_submissions' => 100,
            'expires_at' => now()->addDays(7),
        ], $invitation));

        return [
            'event' => $eventModel,
            'questionnaire' => $questionnaireModel,
            'q' => $q,
            'token' => $token,
            'invitation' => $invitationModel,
        ];
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

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }
}