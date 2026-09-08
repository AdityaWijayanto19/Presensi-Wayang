@props(['hover' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-slate-200' . ($hover ? ' hover:shadow-md transition-shadow cursor-pointer' : '')]) }}>
    {{ $slot }}
</div>
