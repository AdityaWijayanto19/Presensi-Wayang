@props(['color' => 'secondary', 'variant' => 'solid'])

@php
$solid = [
    'primary'   => 'bg-blue-600 text-white',
    'success'   => 'bg-green-600 text-white',
    'danger'    => 'bg-red-600 text-white',
    'warning'   => 'bg-yellow-500 text-white',
    'info'      => 'bg-cyan-500 text-white',
    'secondary' => 'bg-slate-500 text-white',
];
$light = [
    'primary'   => 'bg-blue-100 text-blue-700',
    'success'   => 'bg-green-100 text-green-700',
    'danger'    => 'bg-red-100 text-red-700',
    'warning'   => 'bg-yellow-100 text-yellow-700',
    'info'      => 'bg-cyan-100 text-cyan-700',
    'secondary' => 'bg-slate-100 text-slate-600',
];
$class = $variant === 'light' ? ($light[$color] ?? $light['secondary']) : ($solid[$color] ?? $solid['secondary']);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $class"]) }}>
    {{ $slot }}
</span>
