@php($previousGroup = null)
<aside class="k-sidebar" id="k-sidebar" aria-label="Sidebar">
    <nav class="k-nav" aria-label="Main navigation">
        @foreach ($navItems as $item)
            @if (! empty($item['group']) && $item['group'] !== $previousGroup)
                <p class="k-nav-group">{{ $item['group'] }}</p>
                @php($previousGroup = $item['group'])
            @endif

            @php($isActive = $active === $item['key'])
            <a href="{{ route($item['route']) }}"
               class="k-nav-link {{ $isActive ? 'is-active' : '' }}"
               title="{{ $item['label'] }}"
               @if ($isActive) aria-current="page" @endif>
                <svg class="k-icon" aria-hidden="true"><use href="#i-{{ $item['icon'] }}"></use></svg>
                <span class="k-nav-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <button type="button" class="k-collapse" id="k-collapse" aria-label="Collapse sidebar" aria-expanded="true">
        <svg class="k-icon" aria-hidden="true"><use href="#i-chevrons-left"></use></svg>
        <span class="k-nav-label">Collapse</span>
    </button>
</aside>