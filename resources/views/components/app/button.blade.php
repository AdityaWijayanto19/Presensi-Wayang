@props(['variant' => 'primary', 'size' => 'md'])

@php
$variants = [
    'primary'   => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'warning'   => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
    'info'      => 'bg-cyan-500 text-white hover:bg-cyan-600 focus:ring-cyan-500',
    'ghost'     => 'text-slate-600 hover:bg-slate-100 focus:ring-slate-500',
    'outline'   => 'border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-500',
];
$sizes = [
    'xs' => 'px-2 py-1 text-xs',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-sm',
];
$class = $variants[$variant] ?? $variants['primary'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-1.5 rounded font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 $class $sizeClass"]) }}>
    {{ $slot }}
</button>
