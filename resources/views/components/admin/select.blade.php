@props(['label' => null, 'error' => null, 'placeholder' => null])

<div>
    @if($label)
        <label class="block text-xs font-medium text-slate-600 mb-0.5">{!! $label !!}</label>
    @endif
    <select {{ $attributes->merge(['class' => 'w-full mb-2 rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors bg-white']) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
    @if($error)
        <p class="mt-0.5 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
