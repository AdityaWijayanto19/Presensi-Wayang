@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconSize' => null,
    'type' => 'button',
    'href' => null,
    'target' => null,
    'block' => false,
])

@php
    $iconPx = $iconSize ?? ($size === 'sm' ? 12 : 16);

    $base = 'inline-flex items-center transition-colors font-medium';

    $blockClass = $block ? 'w-full justify-center' : '';

    $variants = [
        'primary'   => 'bg-blue-600 text-white hover:bg-blue-700',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700',
        'success'   => 'bg-green-600 text-white hover:bg-green-700',
        'edit'      => 'bg-cyan-500 text-white hover:bg-cyan-600',
        'warning'   => 'bg-amber-500 text-white hover:bg-amber-600',
        'secondary' => 'border border-slate-300 text-slate-700 hover:bg-slate-50',
        'ghost'     => 'text-slate-400 hover:text-slate-600',
    ];

    $sizes = [
        'md' => 'gap-2 px-3 py-2 rounded-md text-sm',
        'sm' => 'px-1.5 py-1.5 rounded text-[10px]',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . $blockClass;
@endphp

@if($href)
    <a href="{{ $href }}"
       @if($target) target="{{ $target }}" @endif
       {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i data-lucide="{{ $icon }}" style="width:{{ $iconPx }}px;height:{{ $iconPx }}px;"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i data-lucide="{{ $icon }}" style="width:{{ $iconPx }}px;height:{{ $iconPx }}px;"></i>
        @endif
        {{ $slot }}
    </button>
@endif
