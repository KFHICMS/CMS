<?php

namespace Tests\Feature;

use App\Models\EnumeratorInvitation;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use App\Services\SubmissionPhotoStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicInvitationPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_a_photo_is_required(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d);
        unset($payload['photo']);

        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, QuestionnaireResponse::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_a_valid_photo_is_stored_privately_as_a_resized_jpeg(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d, UploadedFile::fake()->image('my-child-photo.png', 2400, 1800));

        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertRedirect(route('public.invitations.thanks', $d['token']));

        $response = QuestionnaireResponse::query()->firstOrFail();

        $this->assertStringStartsWith('public-submissions/'.$d['event']->id.'/', $response->photo_path);
        $this->assertStringEndsWith('.jpg', $response->photo_path);
        $this->assertStringNotContainsString('my-child-photo', $response->photo_path);
        $this->assertSame('local', $response->photo_disk);
        $this->assertNotSame('public', $response->photo_disk);
        Storage::disk('local')->assertExists($response->photo_path);

        $size = getimagesizefromstring(Storage::disk('local')->get($response->photo_path));
        $this->assertSame('image/jpeg', $size['mime']);
        $this->assertLessThanOrEqual(1600, max($size[0], $size[1]));
    }

    public function test_invalid_files_are_rejected_and_nothing_is_stored(): void
    {
        $d = $this->setUpData();
        $url = route('public.invitations.store', $d['token']);

        $bad = [
            UploadedFile::fake()->create('notes.jpg', 10, 'text/plain'),
            UploadedFile::fake()->create('drawing.svg', 5, 'image/svg+xml'),
            UploadedFile::fake()->create('scan.pdf', 50, 'application/pdf'),
            UploadedFile::fake()->image('big.jpg', 200, 200)->size(6000),
        ];

        foreach ($bad as $file) {
            $this->post($url, $this->payload($d, $file))->assertSessionHasErrors('photo');
        }

        $this->assertSame(0, QuestionnaireResponse::query()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_retrying_with_the_same_key_stores_only_one_photo(): void
    {
        $d = $this->setUpData();
        $url = route('public.invitations.store', $d['token']);
        $key = (string) Str::uuid();

        $this->post($url, $this->payload($d, null, $key));
        $this->post($url, $this->payload($d, null, $key))
            ->assertRedirect(route('public.invitations.thanks', $d['token']));

        $this->assertSame(1, QuestionnaireResponse::query()->count());
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_a_failed_validation_leaves_no_photo_behind(): void
    {
        $d = $this->setUpData();
        $payload = $this->payload($d);
        unset($payload['question_'.$d['question']->id]);

        $this->post(route('public.invitations.store', $d['token']), $payload)
            ->assertSessionHasErrors('question_'.$d['question']->id);

        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_the_photo_service_can_remove_a_stored_file(): void
    {
        $service = app(SubmissionPhotoStore::class);
        $stored = $service->store(UploadedFile::fake()->image('a.jpg', 300, 200), 1);

        Storage::disk($stored['disk'])->assertExists($stored['path']);

        $service->delete($stored['disk'], $stored['path']);

        Storage::disk($stored['disk'])->assertMissing($stored['path']);
    }

    public function test_only_admins_can_view_the_photo(): void
    {
        $d = $this->setUpData();
        $this->post(route('public.invitations.store', $d['token']), $this->payload($d));
        $submission = QuestionnaireResponse::query()->firstOrFail();

        $this->get(route('admin_submissions.photo', $submission))->assertRedirect(route('login'));

        foreach (['field_officer', 'enumerator', 'child_officer'] as $role) {
            $this->actingAs($this->user($role))
                ->get(route('admin_submissions.photo', $submission))
                ->assertForbidden();
        }

        $this->actingAs($this->user('admin'))
            ->get(route('admin_submissions.photo', $submission))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_a_submission_without_a_photo_returns_not_found(): void
    {
        $d = $this->setUpData();
        $submission = QuestionnaireResponse::create([
            'questionnaire_id' => $d['questionnaire']->id,
            'child_id' => null,
            'submitted_by' => null,
            'submitted_at' => now(),
            'response_uuid' => (string) Str::uuid(),
        ]);

        $this->actingAs($this->user('admin'))
            ->get(route('admin_submissions.photo', $submission))
            ->assertNotFound();
    }

    private function payload(array $d, ?UploadedFile $photo = null, ?string $key = null): array
    {
        return [
            'respondent_name' => 'Aman Perera',
            'submission_key' => $key ?? (string) Str::uuid(),
            'child_mode' => 'new',
            'question_'.$d['question']->id => 'Lives with grandparents',
            'photo' => $photo ?? UploadedFile::fake()->image('child.jpg', 600, 400),
        ];
    }

    private function setUpData(): array
    {
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
        $question = $questionnaire->questions()->create([
            'question_text' => 'Living situation',
            'question_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $token = EnumeratorInvitation::generateToken();
        EnumeratorInvitation::create([
            'event_id' => $event->id,
            'questionnaire_id' => $questionnaire->id,
            'token_hash' => EnumeratorInvitation::hashToken($token),
            'token_hint' => substr($token, -4),
            'max_submissions' => 100,
            'expires_at' => now()->addDays(7),
        ]);

        return ['event' => $event, 'questionnaire' => $questionnaire, 'question' => $question, 'token' => $token];
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