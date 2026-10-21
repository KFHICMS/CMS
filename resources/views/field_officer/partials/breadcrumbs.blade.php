@php
    $parent = collect($crumbs)->filter(fn ($crumb) => ! empty($crumb['url']))->last();
@endphp
<style>
    .crumbs ol { display: flex; flex-wrap: wrap; align-items: center; gap: 0; margin: 0 0 6px; padding: 0; list-style: none; font-size: 14px; }
    .crumbs li + li::before { content: "›"; margin: 0 8px; color: #8aa093; }
    .crumbs li[hidden] { display: none; }
    .crumbs [aria-current="page"] { font-weight: 700; }
    .crumb-back { display: inline-block; margin-bottom: 10px; padding: 8px 0; }
</style>
<nav class="crumbs" aria-label="Breadcrumb">
    <ol>
        @foreach ($crumbs as $crumb)
            <li @if (($crumb['label'] ?? '') === '') hidden @endif>
                @if (! empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" @if (! empty($crumb['id'])) id="{{ $crumb['id'] }}" @endif>{{ $crumb['label'] }}</a>
                @else
                    <span @if ($loop->last) aria-current="page" @endif @if (! empty($crumb['id'])) id="{{ $crumb['id'] }}" @endif>{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@if ($parent)
    <a class="crumb-back" href="{{ $parent['url'] }}">← Back to {{ $parent['label'] }}</a>
@endif