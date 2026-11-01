<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_enumerator_pages_use_the_shared_shell(): void
    {
        $enumerator = $this->user('enumerator', 'Neha Silva');

        $this->actingAs($enumerator)
            ->get(route('enumerator.dashboard'))
            ->assertOk()
            ->assertSee('css/kfhi-ui.css', false)
            ->assertSee('Skip to main content')
            ->assertSee('KFHI')
            ->assertSee('Child Management System')
            ->assertSee('Neha Silva')
            ->assertSee('Enumerator')
            ->assertSee('Log out')
            ->assertSee('My Submissions')
            ->assertSee('aria-current="page"', false)
            ->assertSee('Welcome, Neha Silva');
    }

    public function test_sidebar_shows_only_the_links_for_the_users_role(): void
    {
        $expected = [
            'admin' => ['Children', 'Guardians', 'Programs', 'Events', 'Questionnaire Submissions', 'Follow-ups', 'Users'],
            'field_officer' => ['My Programs', "Today&#039;s Activities", 'Follow-ups', 'Reports'],
            'enumerator' => ['Dashboard', 'My Submissions'],
            'child_officer' => ['Children', 'Scan QR'],
        ];

        $forbidden = [
            'admin' => ['My Submissions', 'Scan QR'],
            'field_officer' => ['Users', 'Guardians', 'Questionnaire Submissions', 'My Submissions'],
            'enumerator' => ['Users', 'Children', 'Reports', 'Programs'],
            'child_officer' => ['Users', 'Reports', 'My Submissions'],
        ];

        foreach ($expected as $role => $labels) {
            $html = $this->renderShell($this->user($role), 'dashboard');

            foreach ($labels as $label) {
                $this->assertStringContainsString($label, $html, "$role should see $label");
            }

            foreach ($forbidden[$role] as $label) {
                $this->assertStringNotContainsString('title="'.$label.'"', $html, "$role should not see $label");
            }
        }
    }

    public function test_other_roles_do_not_get_admin_links(): void
    {
        $html = $this->renderShell($this->user('enumerator'), 'dashboard');

        $this->assertStringNotContainsString('/admin/', $html);
        $this->assertStringNotContainsString('/field-officer/', $html);
        $this->assertStringNotContainsString('/child-officer/', $html);
    }

    public function test_links_to_routes_that_do_not_exist_are_skipped(): void
    {
        config(['kfhi_navigation.roles.enumerator' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'enumerator.dashboard', 'icon' => 'dashboard'],
            ['key' => 'ghost', 'label' => 'Ghost Page', 'route' => 'does.not.exist', 'icon' => 'folder'],
        ]]);

        $html = $this->renderShell($this->user('enumerator'), 'dashboard');

        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringNotContainsString('Ghost Page', $html);
    }

    public function test_the_active_page_is_marked_and_others_are_not(): void
    {
        $html = $this->renderShell($this->user('enumerator'), 'submissions');

        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('/is-active[^>]*aria-current="page"[^>]*title="My Submissions"|title="My Submissions"[^>]*aria-current="page"/s', $html);
    }

    public function test_notification_bell_is_hidden_until_the_feature_is_enabled(): void
    {
        $enumerator = $this->user('enumerator');

        $this->assertStringNotContainsString('aria-label="Notifications"', $this->renderShell($enumerator, 'dashboard'));

        config(['kfhi_navigation.notifications' => true]);

        $this->assertStringContainsString('aria-label="Notifications"', $this->renderShell($enumerator, 'dashboard'));
    }

    public function test_profile_menu_shows_name_role_and_a_logout_form(): void
    {
        $html = $this->renderShell($this->user('field_officer', 'Kamal Perera'), 'dashboard');

        $this->assertStringContainsString('Kamal Perera', $html);
        $this->assertStringContainsString('Field Officer', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('Log out', $html);
    }

    public function test_breadcrumbs_render_links_and_mark_the_current_page(): void
    {
        $this->actingAs($this->user('enumerator'));

        $html = Blade::render(<<<'BLADE'
@extends('layouts.app', ['active' => 'dashboard', 'crumbs' => [
    ['label' => 'Dashboard', 'url' => '/enumerator/dashboard'],
    ['label' => 'Assigned Events', 'url' => '/enumerator/dashboard'],
    ['label' => 'Child Health Day'],
]])
@section('heading', 'Page')
@section('content') body @endsection
BLADE, [], true);

        $this->assertStringContainsString('aria-label="Breadcrumb"', $html);
        $this->assertStringContainsString('<a href="/enumerator/dashboard">Dashboard</a>', $html);
        $this->assertStringContainsString('<a href="/enumerator/dashboard">Assigned Events</a>', $html);
        $this->assertMatchesRegularExpression('/<span\s+aria-current="page"\s*>Child Health Day<\/span>/', $html);
    }

    public function test_the_shell_shows_flash_messages(): void
    {
        $enumerator = $this->user('enumerator');

        $this->actingAs($enumerator)
            ->withSession(['success' => 'Saved it.', 'error' => 'Something went wrong.'])
            ->get(route('enumerator.dashboard'))
            ->assertSee('Saved it.')
            ->assertSee('Something went wrong.');
    }

    public function test_guests_are_still_sent_to_login(): void
    {
        $this->get(route('enumerator.dashboard'))->assertRedirect(route('login'));
    }

    private function renderShell(User $user, string $active): string
    {
        $this->actingAs($user);

        return Blade::render(<<<'BLADE'
@extends('layouts.app', ['active' => $active])
@section('heading', 'Test page')
@section('content') body @endsection
BLADE, ['active' => $active], true);
    }

    private function user(string $role, string $name = 'Test User'): User
    {
        return User::create([
            'name' => $name,
            'username' => $role.'-'.uniqid(),
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }
}