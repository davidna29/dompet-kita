@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-xs text-muted">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} baris
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-ghost btn-sm cursor-not-allowed opacity-45" aria-disabled="true">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-ghost btn-sm">Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-primary btn-sm">Berikutnya</a>
            @else
                <span class="btn btn-ghost btn-sm cursor-not-allowed opacity-45" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
