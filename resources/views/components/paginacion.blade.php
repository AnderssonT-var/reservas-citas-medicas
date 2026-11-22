@if ($paginator->hasPages())
<nav class="pager" aria-label="Paginación">
    <span>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
    <div class="cluster">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm" aria-disabled="true" style="opacity:.45"><x-icon name="chevron-left" size="16"/> Anterior</span>
        @else
            <a class="btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" size="16"/> Anterior</a>
        @endif
        @if ($paginator->hasMorePages())
            <a class="btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente <x-icon name="chevron-right" size="16"/></a>
        @else
            <span class="btn btn-sm" aria-disabled="true" style="opacity:.45">Siguiente <x-icon name="chevron-right" size="16"/></span>
        @endif
    </div>
</nav>
@endif
