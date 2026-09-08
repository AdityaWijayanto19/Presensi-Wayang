@props(['label' => null, 'icon' => null])

<div>
    @if($label)
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
    @endif
    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                {!! $icon !!}
            </div>
        @endif
        <input {{ $attributes->merge(['class' => 'w-full ' . ($icon ? 'pl-10' : '') . ' pr-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors']) }}>
    </div>
</div>
