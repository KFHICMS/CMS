@props(['items' => []])

@php
    $parent = collect($items)->filter(fn ($item) => ! empty($item['url']))->last();
@endphp

@if (count($items))
    <nav class="k-crumbs" aria-label="Breadcrumb">
        <ol>
            @foreach ($items as $item)
                <li{!! ($item['label'] ?? '') === '' ? ' hidden' : '' !!}>
                    @if (! $loop->last && ! empty($item['url']))
                        <a href="{{ $item['url'] }}"{!! ! empty($item['id']) ? ' id="'.e($item['id']).'"' : '' !!}>{{ $item['label'] }}</a>
                    @else
                        <span{!! $loop->last ? ' aria-current="page"' : '' !!}{!! ! empty($item['id']) ? ' id="'.e($item['id']).'"' : '' !!}>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    @if ($parent && count($items) > 1)
        <a class="k-back" href="{{ $parent['url'] }}">← Back to {{ $parent['label'] }}</a>
    @endif
@endif