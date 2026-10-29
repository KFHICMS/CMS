@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="k-pagination">
            @if ($paginator->onFirstPage())
                <li class="is-disabled" aria-disabled="true"><span aria-hidden="true">&lsaquo; Previous</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Previous</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a></li>
            @else
                <li class="is-disabled" aria-disabled="true"><span aria-hidden="true">Next &rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif