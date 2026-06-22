@if ($paginator->hasPages())
    <nav class="admin-pagination" aria-label="Phân trang">
        <span class="admin-pagination-info">Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }}</span>
        <div class="admin-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="admin-page disabled">‹</span>
            @else
                <a class="admin-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-page disabled">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="admin-page active">{{ $page }}</span>
                        @else
                            <a class="admin-page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="admin-page" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
            @else
                <span class="admin-page disabled">›</span>
            @endif
        </div>
    </nav>
@endif

<style>
    .admin-pagination { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; padding:16px 0 0; }
    .admin-pagination-info { color:#7f8c8d; font-size:12px; font-weight:600; }
    .admin-pagination-links { display:flex; align-items:center; gap:6px; }
    .admin-page { display:inline-flex; min-width:32px; height:32px; align-items:center; justify-content:center; border:1px solid #e5e7eb; border-radius:8px; background:#fff; color:#334155; font-size:13px; font-weight:700; text-decoration:none; }
    .admin-page:hover:not(.disabled):not(.active) { border-color:#86efac; background:#ecfdf5; color:#047857; }
    .admin-page.active { background:#2ecc71; border-color:#2ecc71; color:#fff; }
    .admin-page.disabled { color:#cbd5e1; background:#f8fafc; cursor:not-allowed; }
</style>
