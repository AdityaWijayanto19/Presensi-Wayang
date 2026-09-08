@props(['id', 'title', 'size' => 'md'])

@php
$sizes = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal-{{ $id }}.window="open = true"
     x-on:close-modal.window="if(event.detail?.id === '{{ $id }}') open = false"
     x-show="open"
     x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50"
     {{ $attributes }}>

    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

    <div class="fixed inset-0 flex items-center justify-center p-3">
        <div class="bg-white rounded-lg shadow-xl {{ $sizes[$size] }} w-full max-h-[90vh] overflow-y-auto"
             x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            <div class="flex items-center justify-between px-4 py-2.5 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-800">{{ $title }}</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
