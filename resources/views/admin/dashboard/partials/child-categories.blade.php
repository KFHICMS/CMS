{{-- Child category donut. Data comes from App\Services\ChildCategoryStats via a view composer. --}}
<section class="k-card" aria-labelledby="child-cat-h">
    <div class="k-card-head">
        <h2 id="child-cat-h">Children by category</h2>
        <span>{{ number_format($categoryStats['children_any']) }} {{ \Illuminate\Support\Str::plural('child', $categoryStats['children_any']) }} with a categorised record</span>
    </div>

    @if ($categoryStats['chart_total'] > 0)
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 28px">
            <figure style="margin: 0">
                <svg viewBox="0 0 42 42" width="230" height="230" role="img" aria-labelledby="donut-title donut-desc">
                    <title id="donut-title">Children by category</title>
                    <desc id="donut-desc">
                        @foreach ($categoryStats['segments'] as $s){{ $s['label'] }}: {{ $s['children'] }} children. @endforeach
                    </desc>
                    <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#E8F5E9" stroke-width="6"></circle>
                    @foreach ($categoryStats['segments'] as $s)
                        @if ($s['children'] > 0)
                            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $s['color'] }}" stroke-width="6"
                                    stroke-dasharray="{{ $s['dash'] }} {{ $s['gap'] }}" stroke-dashoffset="{{ $s['offset'] }}">
                                <title>{{ $s['label'] }}: {{ $s['children'] }} {{ \Illuminate\Support\Str::plural('child', $s['children']) }} ({{ $s['pct'] }}% of the chart)</title>
                            </circle>
                        @endif
                    @endforeach
                    <text x="21" y="21" text-anchor="middle" font-size="6.5" font-weight="700" fill="#05500d">{{ $categoryStats['children_any'] }}</text>
                    <text x="21" y="26" text-anchor="middle" font-size="2.6" fill="#546E7A">children</text>
                </svg>
            </figure>

            <ul style="list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; min-width: 240px; flex: 1">
                @foreach ($categoryStats['segments'] as $s)
                    <li style="display: flex; align-items: center; gap: 10px">
                        <span aria-hidden="true" style="width: 14px; height: 14px; border-radius: 3px; background: {{ $s['color'] }}; flex: none"></span>
                        <span style="flex: 1"><strong>{{ $s['label'] }}</strong></span>
                        <span>{{ number_format($s['children']) }} {{ \Illuminate\Support\Str::plural('child', $s['children']) }} <span class="muted">({{ $s['pct'] }}%)</span></span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="k-empty">No categorised records yet. Categories appear here once benefits, field notes or follow-ups with a category such as Education, Health, Nutrition or Shelter are recorded.</div>
    @endif

    <details style="margin-top: 14px">
        <summary class="muted" style="cursor: pointer">How children are counted</summary>
        <div class="muted" style="margin-top: 8px">
            <p>A child is counted once in a category when they have at least one qualifying record whose category text matches that category. Several records for the same child in the same category still count as one child.</p>
            <p>A child with records in different categories is counted once in each, so the four totals can add up to more than the number of children. The centre number counts every child once.</p>
            <p>Qualifying records: benefits and field notes from <em>submitted</em> field records (drafts are ignored) and follow-ups that are not cancelled. Archived children are never counted.</p>
            @if ($categoryStats['other_records'] > 0)
                <p>{{ number_format($categoryStats['other_records']) }} {{ \Illuminate\Support\Str::plural('record', $categoryStats['other_records']) }} use a category that matches none of the four groups and {{ $categoryStats['other_records'] === 1 ? 'is' : 'are' }} not shown in the chart.</p>
            @endif
        </div>
    </details>
</section>