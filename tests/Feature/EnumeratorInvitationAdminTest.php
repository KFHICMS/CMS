<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EnumeratorInvitation;
use App\Models\Event;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnumeratorInvitationAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_generates_a_link_and_only_the_hash_is_stored(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        $showUrl = route('events.questionnaires.show', [$event, $questionnaire]);

        $this->actingAs($admin)
            ->post(route('events.questionnaires.invitations.store', [$event, $questionnaire]), [
                'expires_in_days' => 7,
                'max_submissions' => 100,
            ])
            ->assertRedirect($showUrl)
            ->assertSessionHas('new_invitation_url');

        $url = session('new_invitation_url');
        $this->assertStringStartsWith(url('/join'), $url);
        $token = Str::after($url, '/join/');
        $this->assertSame(43, strlen($token));

        $invitation = EnumeratorInvitation::query()->firstOrFail();
        $this->assertSame(hash('sha256', $token), $invitation->token_hash);
        $this->assertSame(substr($token, -4), $invitation->token_hint);
        $this->assertSame(100, $invitation->max_submissions);
        $this->assertSame($admin->id, $invitation->created_by);
        $this->assertTrue($invitation->expires_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));
        $this->assertDatabaseMissing('enumerator_invitations', ['token_hash' => $token]);

        $log = AuditLog::query()->where('action', 'invitation_created')->firstOrFail();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringNotContainsString($token, (string) $log->description);

        // The one-time link is visible on the page right after creating it, then gone.
        $this->assertNotNull($invitation->token_encrypted);
        $this->assertSame($token, Crypt::decryptString($invitation->token_encrypted));
        $this->assertStringNotContainsString($token, $invitation->token_encrypted);

        // The link stays copyable from the admin table.
        $this->get($showUrl)->assertOk()->assertSee($url, false)->assertSee('Copy link');
        $this->get($showUrl)->assertOk()->assertSee($url, false);
    }

    public function test_links_cannot_be_generated_for_unpublished_questionnaires_or_with_bad_settings(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        $url = route('events.questionnaires.invitations.store', [$event, $questionnaire]);

        $questionnaire->update(['status' => 'draft']);
        $this->actingAs($admin)
            ->post($url, ['expires_in_days' => 7, 'max_submissions' => 100])
            ->assertSessionHas('error');

        $questionnaire->update(['status' => 'published']);
        $this->post($url, ['expires_in_days' => 400, 'max_submissions' => 100])
            ->assertSessionHasErrors('expires_in_days');
        $this->post($url, ['expires_in_days' => 7, 'max_submissions' => 0])
            ->assertSessionHasErrors('max_submissions');

        $this->assertSame(0, EnumeratorInvitation::query()->count());
    }

    public function test_admin_can_revoke_a_link(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        $invitation = $this->invitation($questionnaire, $admin);

        $this->actingAs($admin)
            ->post(route('events.questionnaires.invitations.revoke', [$event, $questionnaire, $invitation]))
            ->assertRedirect(route('events.questionnaires.show', [$event, $questionnaire]));

        $fresh = $invitation->fresh();
        $this->assertSame('revoked', $fresh->status());
        $this->assertSame($admin->id, $fresh->revoked_by);
        $this->assertFalse($fresh->isUsable());
        $this->assertDatabaseHas('audit_logs', ['action' => 'invitation_revoked']);

        // Revoking twice is harmless.
        $this->post(route('events.questionnaires.invitations.revoke', [$event, $questionnaire, $invitation]))
            ->assertRedirect();
    }

    public function test_replace_revokes_the_old_link_and_issues_a_new_one(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        $old = $this->invitation($questionnaire, $admin);

        $this->actingAs($admin)
            ->post(route('events.questionnaires.invitations.replace', [$event, $questionnaire, $old]), [
                'expires_in_days' => 7,
                'max_submissions' => 100,
            ])
            ->assertSessionHas('new_invitation_url');

        $this->assertSame('revoked', $old->fresh()->status());
        $this->assertSame(2, EnumeratorInvitation::query()->count());

        $new = EnumeratorInvitation::query()->whereNull('revoked_at')->firstOrFail();
        $this->assertNotSame($old->token_hash, $new->token_hash);
        $this->assertSame('active', $new->status());

        // A revoked link cannot be replaced.
        $this->post(route('events.questionnaires.invitations.replace', [$event, $questionnaire, $old]), [
            'expires_in_days' => 7,
            'max_submissions' => 100,
        ])->assertSessionHas('error');
        $this->assertSame(2, EnumeratorInvitation::query()->count());
    }

    public function test_status_reflects_expiry_and_revocation(): void
    {
        [$admin, , $questionnaire] = $this->setUpData();

        $active = $this->invitation($questionnaire, $admin);
        $expired = $this->invitation($questionnaire, $admin, ['expires_at' => now()->subMinute()]);
        $revoked = $this->invitation($questionnaire, $admin, ['revoked_at' => now()]);

        $this->assertSame('active', $active->status());
        $this->assertSame('expired', $expired->status());
        $this->assertSame('revoked', $revoked->status());
        $this->assertTrue($active->isUsable());
        $this->assertFalse($expired->isUsable());
        $this->assertFalse($revoked->isUsable());
    }

    public function test_only_admins_can_manage_invitations(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        $invitation = $this->invitation($questionnaire, $admin);

        foreach (['field_officer', 'enumerator', 'child_officer'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)
                ->post(route('events.questionnaires.invitations.store', [$event, $questionnaire]), [
                    'expires_in_days' => 7, 'max_submissions' => 100,
                ])->assertForbidden();
            $this->actingAs($user)
                ->post(route('events.questionnaires.invitations.revoke', [$event, $questionnaire, $invitation]))
                ->assertForbidden();
            $this->actingAs($user)
                ->post(route('events.questionnaires.invitations.replace', [$event, $questionnaire, $invitation]), [
                    'expires_in_days' => 7, 'max_submissions' => 100,
                ])->assertForbidden();
        }

        $this->assertSame('active', $invitation->fresh()->status());
        $this->assertSame(1, EnumeratorInvitation::query()->count());
    }

    public function test_invitations_must_belong_to_the_given_event_and_questionnaire(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();
        [, $otherEvent, $otherQuestionnaire] = $this->setUpData();
        $invitation = $this->invitation($questionnaire, $admin);

        $this->actingAs($admin)
            ->post(route('events.questionnaires.invitations.store', [$event, $otherQuestionnaire]), [
                'expires_in_days' => 7, 'max_submissions' => 100,
            ])->assertNotFound();

        $this->actingAs($admin)
            ->post(route('events.questionnaires.invitations.revoke', [$otherEvent, $otherQuestionnaire, $invitation]))
            ->assertNotFound();

        $this->assertSame('active', $invitation->fresh()->status());
    }

    private function invitation(Questionnaire $questionnaire, User $admin, array $overrides = []): EnumeratorInvitation
    {
        return EnumeratorInvitation::create(array_merge([
            'event_id' => $questionnaire->event_id,
            'questionnaire_id' => $questionnaire->id,
            'token_hash' => hash('sha256', Str::random(43)),
            'token_hint' => 'abcd',
            'max_submissions' => 100,
            'expires_at' => now()->addDays(7),
            'created_by' => $admin->id,
        ], $overrides));
    }

    private function setUpData(): array
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

        return [$admin, $event, $questionnaire];
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

        public function test_only_active_links_can_be_copied_from_the_table(): void
    {
        [$admin, $event, $questionnaire] = $this->setUpData();

        $activeToken = EnumeratorInvitation::generateToken();
        $revokedToken = EnumeratorInvitation::generateToken();

        $this->invitation($questionnaire, $admin, [
            'token_hash' => hash('sha256', $activeToken),
            'token_encrypted' => Crypt::encryptString($activeToken),
        ]);
        $this->invitation($questionnaire, $admin, [
            'token_hash' => hash('sha256', $revokedToken),
            'token_encrypted' => Crypt::encryptString($revokedToken),
            'revoked_at' => now(),
        ]);
        $this->invitation($questionnaire, $admin); // created before copying existed

        $this->actingAs($admin)
            ->get(route('events.questionnaires.show', [$event, $questionnaire]))
            ->assertOk()
            ->assertSee(url('/join/'.$activeToken), false)
            ->assertDontSee(url('/join/'.$revokedToken), false)
            ->assertSee('Use Replace to get a copyable link');
    }
}