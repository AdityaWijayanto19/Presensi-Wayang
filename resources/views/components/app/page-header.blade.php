@props(['pretitle' => null, 'title'])

<div class="bg-white border-b border-slate-200 px-4 py-2 print:hidden">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center gap-2">
            <div class="flex-1">
                @if($pretitle)
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider leading-tight">{{ $pretitle }}</div>
                @endif
                <h2 class="text-base font-semibold text-slate-800 leading-tight">{{ $title }}</h2>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
