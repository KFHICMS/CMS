<?php

namespace App\View\Components;

use App\Support\BackLink;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Shared "← Back" button, rendered once by layouts.app for every page that uses it.
 * Pages influence it only through the data they already pass to the layout:
 *   'crumbs'   => breadcrumb list (the nearest crumb with a URL is the parent page)
 *   'active'   => the sidebar key (falls back to that section's list page)
 *   'backUrl'  => optional explicit same-site URL
 *   'hideBack' => true to suppress the button on a page
 */
class BackButton extends Component
{
    public ?array $link = null;

    public function __construct(
        $crumbs = [],
        public ?string $active = null,
        public ?string $backUrl = null,
        $navItems = [],
        bool $hide = false,
    ) {
        if ($hide) {
            return;
        }

        $request = request();

        $sections = collect($navItems)->map(fn ($item) => [
            'key' => $item['key'] ?? null,
            'label' => $item['label'] ?? '',
            'url' => route($item['route']),
        ])->all();

        $this->link = BackLink::resolve(
            collect($crumbs)->all(),
            $active,
            $backUrl,
            $request->fullUrl(),
            $request->headers->get('referer'),
            $request->getHost(),
            $sections,
        );
    }

    public function shouldRender(): bool
    {
        return $this->link !== null;
    }

    public function render(): View
    {
        return view('components.back-button');
    }
}