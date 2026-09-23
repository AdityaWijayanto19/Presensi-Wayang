@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">

        {{-- Results info --}}
        <div class="text-[13px] text-slate-500">
            @if ($paginator->firstItem())
                {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
            @else
                {{ $paginator->count() }} data
            @endif
        </div>

        {{-- Page links --}}
        <div class="flex items-center gap-1">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-900 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 text-sm">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-900 text-white text-sm font-medium">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 text-sm hover:bg-slate-900 hover:text-white transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-900 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif

        </div>
    </nav>
@endif
