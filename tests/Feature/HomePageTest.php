<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_renders_every_section_and_links_to_the_existing_login_route(): void
    {
        $html = $this->get('/')
            ->assertOk()
            ->assertSee('Empowering Children,', false)
            ->assertSee('Building Brighter Futures', false)
            ->assertSee('Get Started')
            ->assertSee('Learn More')
            ->assertSee('css/kfhi-landing.css', false)
            ->assertSee('images/logo.png', false)
            ->getContent();

        foreach (['home', 'features', 'about', 'services', 'team'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, "section #$id is missing");
        }

        foreach (['#home', '#about', '#services', '#team'] as $anchor) {
            $this->assertStringContainsString('href="'.$anchor.'"', $html, "nav link $anchor is missing");
        }

        // Login button, "Get Started" and the footer link all use the existing named route.
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.route('login').'"'));
        $this->assertStringContainsString('aria-controls="kl-nav"', $html);
        $this->assertStringContainsString('Skip to main content', $html);
    }

    public function test_the_landing_page_does_not_use_the_dashboard_stylesheet(): void
    {
        $this->get('/')->assertOk()->assertDontSee('css/kfhi-ui.css', false);
    }

    public function test_the_landing_styles_are_not_loaded_by_the_authenticated_dashboards(): void
    {
        foreach (['admin', 'field_officer', 'enumerator'] as $role) {
            $user = User::create([
                'name' => 'Test '.$role,
                'username' => $role.'-'.uniqid(),
                'email' => $role.'-'.uniqid().'@example.test',
                'password' => 'password',
                'role' => $role,
                'status' => 'active',
            ]);

            $this->actingAs($user)
                ->get(route($role.'.dashboard'))
                ->assertOk()
                ->assertSee('css/kfhi-ui.css', false)
                ->assertDontSee('kfhi-landing.css', false);
        }
    }

    public function test_the_landing_page_invents_no_contact_details(): void
    {
        $this->get('/')->assertOk()
            ->assertDontSee('mailto:', false)
            ->assertDontSee('tel:', false)
            ->assertDontSee('facebook.com', false);
    }
}