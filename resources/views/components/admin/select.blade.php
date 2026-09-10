@props(['label' => null, 'error' => null, 'placeholder' => null, 'searchable' => false])

@php
    $name = $attributes->get('name');
    $id = $attributes->get('id');
    $isRequired = $attributes->has('required');
@endphp

@if ($searchable)
    <div class="mb-2" x-data="searchableSelect()" x-init="init()" @keydown.escape.window="close()"
        @options-updated.window="refreshOptions()">
        @if ($label)
            <label class="block text-xs font-medium text-slate-600 mb-1">{!! $label !!}</label>
        @endif

        <input type="hidden" name="{{ $name }}" :value="selectedValue"
            @if ($isRequired) required @endif>

        <div class="relative w-full">
            <button type="button" @click="toggle()"
                class="block h-[42px] w-full appearance-none rounded-md border border-slate-300 bg-white px-3 pr-9 py-2 text-sm text-left transition-colors cursor-pointer"
                :class="open ? 'ring-2 ring-blue-500 border-blue-500' : 'hover:border-slate-400'">
                <span class="truncate" :class="selectedLabel ? 'text-slate-700' : 'text-slate-400'"
                    x-text="selectedLabel || '{{ addslashes($placeholder ?: 'Pilih...') }}'"></span>
            </button>
            <span
                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''">
                <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
            </span>

            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                @click.away="close()"
                class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg overflow-hidden">

                <div class="p-1.5 border-b border-slate-100">
                    <div class="relative">
                        <i data-lucide="search"
                            class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400"></i>
                        <input type="text" x-model.debounce.200ms="search" @keydown="handleKeydown($event)"
                            x-ref="searchInput" placeholder="Cari..."
                            class="w-full pl-7 pr-2 py-1.5 text-sm border border-slate-200 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="overflow-y-auto max-h-48 py-0.5" x-ref="optionsList">
                    <template x-if="filtered.length === 0">
                        <div class="px-3 py-2 text-sm text-slate-400 text-center">Tidak ditemukan</div>
                    </template>
                    <template x-for="(option, idx) in filtered" :key="option.value">
                        <div @click="select(option)" @mouseenter="highlightedIndex = idx"
                            class="px-3 py-1.5 text-sm cursor-pointer transition-colors flex items-center gap-2"
                            :class="{
                                'bg-blue-50 text-blue-700': idx === highlightedIndex,
                                'bg-blue-50 text-blue-700 font-medium': option.value === selectedValue && idx !==
                                    highlightedIndex,
                                'text-slate-700 hover:bg-slate-50': option.value !== selectedValue && idx !==
                                    highlightedIndex
                            }">
                            <i data-lucide="check" class="w-3.5 h-3.5 shrink-0"
                                :class="option.value === selectedValue ? 'text-blue-600' : 'text-transparent'"></i>
                            <span x-text="option.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <select class="hidden" x-ref="nativeSelect" @if ($id) id="{{ $id }}" @endif>
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        @if ($error)
            <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
        @endif
    </div>
@else
    <div class="mb-2" x-data="{ open: false }">
        @if ($label)
            <label class="block text-xs font-medium text-slate-600 mb-1">{!! $label !!}</label>
        @endif

        <div class="relative">
            <select
                {{ $attributes->merge(['class' => 'w-full appearance-none rounded-md border border-slate-300 px-3 pr-9 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors bg-white cursor-pointer']) }}
                @focus="open = true" @blur="open = false">
                @if ($placeholder)
                    <option value="">{{ $placeholder }}</option>
                @endif
                {{ $slot }}
            </select>

            <span
                class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''">
                <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
            </span>
        </div>

        @if ($error)
            <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
        @endif
    </div>
@endif
