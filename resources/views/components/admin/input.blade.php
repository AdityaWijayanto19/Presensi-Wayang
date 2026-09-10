@props(['label' => null, 'error' => null, 'type' => 'text', 'icon' => null])

<div class="mb-2">
    @if($label)
        <label class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
    @endif

    <div class="relative flex items-center">
        @if($icon)
            <span class="absolute left-3 flex items-center justify-center text-slate-400 pointer-events-none">
                {!! $icon !!}
            </span>
        @endif

        <input type="{{ $type }}" {{ $attributes->merge([
            'class' => 'w-full rounded-md border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors ' . ($icon ? 'pl-9 pr-3 py-2' : 'px-3 py-2')
        ]) }}>
    </div>

    @if($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
