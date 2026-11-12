@if ($paginator->hasPages())
<nav role="navigation" aria-label="Navigasi halaman" class="batik-pager">
  <div class="batik-pager-links">
    {{-- Sebelumnya --}}
    @if ($paginator->onFirstPage())
      <span class="batik-page-btn is-disabled">&laquo;</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="batik-page-btn" rel="prev">&laquo;</a>
    @endif
    {{-- Nomor --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="batik-page-btn is-disabled">...</span>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="batik-page-btn is-current">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="batik-page-btn">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach
    {{-- Berikutnya --}}
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="batik-page-btn" rel="next">&raquo;</a>
    @else
      <span class="batik-page-btn is-disabled">&raquo;</span>
    @endif
  </div>
</nav>
<style>
  .batik-pager { margin-top: 18px; display: flex; justify-content: center; }
  .batik-pager-links { display: flex; gap: 8px; flex-wrap: wrap; }
  .batik-page-btn { min-width: 40px; height: 40px; padding: 0 12px; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: .9rem; color: var(--color-primary); background: #fff; border: 1px solid var(--color-border); text-decoration: none; }
  a.batik-page-btn:hover { background: var(--color-accent); border-color: var(--color-accent); color: #fff; }
  .batik-page-btn.is-current { background: var(--color-primary); border-color: var(--color-primary); color: #fff; box-shadow: 0 4px 12px var(--color-primary-glow); }
  .batik-page-btn.is-disabled { opacity: .45; }
</style>
@endif
