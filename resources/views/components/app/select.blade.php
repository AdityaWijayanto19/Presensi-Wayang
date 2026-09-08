@props(['label' => null, 'options' => []])

<div>
    @if($label)
        <label class="block text-xs font-medium text-slate-600 mb-0.5">{{ $label }}</label>
    @endif
    <select {{ $attributes->merge(['class' => 'w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white']) }}>
        {{ $slot }}
    </select>
</div>
