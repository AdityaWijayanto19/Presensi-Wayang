@props(['align' => 'right'])

<div x-data="{ open: false }" class="relative" {{ $attributes }}>
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="open = false"
         @keydown.escape.window="open = false"
         class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-1 w-44 bg-white rounded shadow-lg border border-slate-200 py-1 z-50">
        {{ $slot }}
    </div>
</div>
