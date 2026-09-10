@props(['label' => null, 'error' => null, 'type' => 'text', 'icon' => null])

<div>
    @if($label)
        <label class="block text-xs font-medium text-slate-600 mb-0.5">{{ $label }}</label>
    @endif
    <div class="relative">
        @if($icon)
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">{!! $icon !!}</span>
        @endif
        <input type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full mb-2 rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors ' . ($icon ? 'pl-8 pr-3 py-2' : 'px-2.5 py-1.5')]) }}>
    </div>
    @if($error)
        <p class="mt-0.5 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
