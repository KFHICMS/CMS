<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Child;
use App\Models\EnumeratorInvitation;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicInvitationChildTest extends TestCase
{
    use RefreshDatabase;

    public function test_identify_returns_only_the_child_code_and_a_proof(): void
    {
        $d = $this->setUpData();

        $this->postJson(route('public.invitations.identify', $d['token']), ['qr' => $d['qrUrl']])
            ->assertOk()
            ->assertJsonPath('child.child_code', 'KFHI-QR1')
            ->assertJsonStructure(['success', 'child' => ['child_code'], 'proof'])
            ->assertJsonMissingPath('child.id')
            ->assertJsonMissingPath('child.name');
    }

    public function test_submission_with_a_valid_proof_is_linked_to_the_child(): void
    {
        $d = $this->setUpData();
        $proof = $this->identify($d);

        $this->post(route('public.invitations.store', $d['token']), $this->payload($d, $proof))
            ->assertRedirect(route('public.invitations.thanks', $d['token']));

        $response = QuestionnaireResponse::query()->firstOrFail();
        $this->assertSame($d['child']->id, $response->child_id);
        $this->assertNull($response->submitted_by);
        $this->assertSame($d['invitation']->id, $response->invitation_id);
        $this->assertSame('pending', $response->review_status);
    }

    public function test_the_same_child_cannot_answer_twice(): void
    {
        $d = $this->setUpData();
        $proof = $this->identify($d);

        $this->post(route('public.invitations.store', $d['token']), $this->payload($d, $proof))->assertRedirect();

        // Scanning the same child again is refused.
        $this->postJson(route('public.invitations.identify', $d['token']), ['qr' => $d['qrUrl']])
            ->assertStatus(409)
            ->assertJsonPath('message', 'This child has already answered this questionnaire.');

        // Reusing the old proof with a new submission key is refused too.
        $this->post(route('public.invitations.store', $d['token']), $this->payload($d, $proof))
            ->assertSessionHasErrors('child_proof');

        $this->assertSame(1, QuestionnaireResponse::query()->count());
    }

    public function test_unknown_qr_codes_and_typed_child_codes_are_rejected(): void
    {
        $d = $this->setUpData();
        $url = route('public.invitations.identify', $d['token']);

        $this->postJson($url, ['qr' => url('/qr/scan/'.str_repeat('z', 64))])->assertNotFound();
        $this->postJson($url, ['qr' => 'not a qr url'])->assertStatus(422);
        $this->postJson($url, ['child_code' => 'KFHI-QR1'])->assertStatus(422);

        $this->assertSame(2, AuditLog::query()->where('action', 'identify_failed')->count());
        foreach (AuditLog::query()->where('action', 'identify_failed')->get() as $log) {
            $this->assertStringNotContainsString('KFHI-QR1', (string) $log->description);
        }
    }

    public function test_forged_expired_and_foreign_proofs_are_rejected(): void
    {
        $d = $this->setUpData();
        $other = $this->setUpData();
        $url = route('public.invitations.store', $d['token']);

        $proof = $this->identify($d);

        $this->post($url, $this->payload($d, 'forged'))->assertSessionHasErrors('child_proof');
        $this->post($url, $this->payload($d, ''))->assertSessionHasErrors('child_proof');

        // A proof issued for another invitation does not work here.
        $foreign = $this->identify($other);
        $this->post($url, $this->payload($d, $foreign))->assertSessionHasErrors('child_proof');

        // Proofs expire after 30 minutes.
        $this->travel(31)->minutes();
        $this->post($url, $this->payload($d, $proof))->assertSessionHasErrors('child_proof');

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_an_inactive_child_cannot_be_submitted_for(): void
    {
        $d = $this->setUpData();
        $proof = $this->identify($d);
        $d['child']->update(['status' => 'inactive']);

        $this->post(route('public.invitations.store', $d['token']), $this->payload($d, $proof))
            ->assertSessionHasErrors('child_proof');

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_a_child_choice_is_required(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d, '');
        unset($payload['child_mode']);

        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertSessionHasErrors('child_mode');

        $this->assertSame(0, QuestionnaireResponse::query()->count());
    }

    public function test_identify_is_blocked_for_unusable_links(): void
    {
        $revoked = $this->setUpData(['revoked_at' => now()]);
        $expired = $this->setUpData(['expires_at' => now()->subMinute()]);
        $closed = $this->setUpData([], ['status' => 'completed']);

        $this->postJson(route('public.invitations.identify', $revoked['token']), ['qr' => $revoked['qrUrl']])->assertStatus(410);
        $this->postJson(route('public.invitations.identify', $expired['token']), ['qr' => $expired['qrUrl']])->assertStatus(410);
        $this->postJson(route('public.invitations.identify', $closed['token']), ['qr' => $closed['qrUrl']])->assertStatus(409);
    }

    private function identify(array $d): string
    {
        return $this->postJson(route('public.invitations.identify', $d['token']), ['qr' => $d['qrUrl']])
            ->assertOk()
            ->json('proof');
    }

    private function payload(array $d, string $proof): array
    {
        return [
            'respondent_name' => 'Aman Perera',
            'submission_key' => (string) Str::uuid(),
            'child_mode' => 'existing',
            'child_proof' => $proof,
            'question_'.$d['question']->id => 'Lives with grandparents',
            'photo' => UploadedFile::fake()->image('child.jpg', 600, 400),
        ];
    }

    private function setUpData(array $invitation = [], array $event = []): array
    {
        $eventModel = Event::create(array_merge([
            'event_code' => 'EVT-'.uniqid(),
            'title' => 'CARP event',
            'event_date' => today(),
            'status' => 'planned',
            'enable_attendance' => true,
            'enable_questionnaires' => true,
        ], $event));
        $questionnaire = Questionnaire::create([
            'event_id' => $eventModel->id,
            'title' => 'CARP survey',
            'status' => 'published',
        ]);
        $question = $questionnaire->questions()->create([
            'question_text' => 'Living situation',
            'question_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $child = Child::create([
            'child_code' => 'KFHI-QR'.(Child::query()->count() + 1),
            'first_name' => 'Hidden',
            'last_name' => 'Child',
            'status' => 'active',
        ]);
        $qrPlain = Str::random(64);
        QRCode::create([
            'child_id' => $child->id,
            'token_hash' => hash('sha256', $qrPlain),
            'status' => 'active',
        ]);

        $token = EnumeratorInvitation::generateToken();
        $invitationModel = EnumeratorInvitation::create(array_merge([
            'event_id' => $eventModel->id,
            'questionnaire_id' => $questionnaire->id,
            'token_hash' => EnumeratorInvitation::hashToken($token),
            'token_hint' => substr($token, -4),
            'max_submissions' => 100,
            'expires_at' => now()->addDays(7),
        ], $invitation));

        return [
            'question' => $question,
            'child' => $child,
            'token' => $token,
            'invitation' => $invitationModel,
            'qrUrl' => url('/qr/scan/'.$qrPlain),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }
}