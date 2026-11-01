<?php

namespace Tests\Unit;

use App\Support\BackLink;
use PHPUnit\Framework\TestCase;

class BackLinkTest extends TestCase
{
    private const HOST = 'kfhi.test';

    private function crumbs(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => 'http://kfhi.test/admin/dashboard'],
            ['label' => 'Programs', 'url' => 'http://kfhi.test/admin/programs'],
            ['label' => 'Edit program'],
        ];
    }

    private function resolve(array $crumbs, ?string $active = null, ?string $explicit = null, string $current = 'http://kfhi.test/admin/programs/3/edit', ?string $referer = null, array $sections = []): ?array
    {
        return BackLink::resolve($crumbs, $active, $explicit, $current, $referer, self::HOST, $sections);
    }

    public function test_points_to_the_nearest_parent_breadcrumb(): void
    {
        $link = $this->resolve($this->crumbs());

        $this->assertSame('http://kfhi.test/admin/programs', $link['url']);
        $this->assertSame('Programs', $link['label']);
    }

    public function test_keeps_filters_and_page_when_the_referrer_is_the_parent_list(): void
    {
        $link = $this->resolve($this->crumbs(), referer: 'http://kfhi.test/admin/programs?status=active&page=3');

        $this->assertSame('http://kfhi.test/admin/programs?status=active&page=3', $link['url']);
    }

    public function test_ignores_a_referrer_that_is_not_the_target_page(): void
    {
        $link = $this->resolve($this->crumbs(), referer: 'http://kfhi.test/admin/children?page=2');

        $this->assertSame('http://kfhi.test/admin/programs', $link['url']);
    }

    public function test_ignores_a_referrer_on_another_site(): void
    {
        $link = $this->resolve($this->crumbs(), referer: 'https://evil.example/admin/programs');

        $this->assertSame('http://kfhi.test/admin/programs', $link['url']);
    }

    public function test_falls_back_to_the_section_list_when_there_are_no_breadcrumbs(): void
    {
        $sections = [['key' => 'children', 'label' => 'Children', 'url' => 'http://kfhi.test/admin/children']];

        $link = $this->resolve([], 'children', null, 'http://kfhi.test/admin/children/9', null, $sections);

        $this->assertSame('http://kfhi.test/admin/children', $link['url']);
        $this->assertSame('Children', $link['label']);
    }

    public function test_hidden_on_a_dashboard(): void
    {
        $sections = [['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'http://kfhi.test/admin/dashboard']];

        $this->assertNull($this->resolve([], 'dashboard', null, 'http://kfhi.test/admin/dashboard', null, $sections));
    }

    public function test_hidden_when_the_target_is_the_current_page(): void
    {
        $crumbs = [['label' => 'Programs', 'url' => 'http://kfhi.test/admin/programs'], ['label' => 'Here']];

        $this->assertNull($this->resolve($crumbs, null, null, 'http://kfhi.test/admin/programs?page=2'));
    }

    public function test_hidden_when_nothing_is_known(): void
    {
        $this->assertNull($this->resolve([], null));
        $this->assertNull($this->resolve([['label' => 'Only current page']], 'unknown'));
    }

    public function test_rejects_unsafe_explicit_urls(): void
    {
        foreach (['https://evil.example/x', '//evil.example/x', 'javascript:alert(1)', "/ok\r\nLocation: x", '/a\\b'] as $bad) {
            $this->assertNull($this->resolve([], null, $bad), "Accepted unsafe URL: {$bad}");
        }
    }

    public function test_accepts_a_same_site_relative_path(): void
    {
        $link = $this->resolve([], null, '/admin/programs');

        $this->assertSame('/admin/programs', $link['url']);
    }

    public function test_trailing_slash_does_not_defeat_the_current_page_check(): void
    {
        $crumbs = [['label' => 'Children', 'url' => 'http://kfhi.test/admin/children/'], ['label' => 'Here']];

        $this->assertNull($this->resolve($crumbs, null, null, 'http://kfhi.test/admin/children'));
    }
}