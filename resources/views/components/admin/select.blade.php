@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => 'Pilih salah satu...',
    'required' => false,
    'value' => null,
    'searchable' => false,
    'autoSubmit' => false,
    'id' => null,
])

@php
    $selectedValue = old($name, $value);

    $formattedOptions = collect($options)
        ->map(function ($label, $key) {
            if (is_array($label)) {
                return [
                    'value' => (string) ($label['id'] ?? ($label['value'] ?? $key)),
                    'label' => (string) ($label['name'] ?? ($label['label'] ?? reset($label))),
                ];
            }
            if (is_object($label)) {
                return [
                    'value' => (string) ($label->id ?? ($label->value ?? $key)),
                    'label' => (string) ($label->name ?? ($label->label ?? '')),
                ];
            }
            return [
                'value' => (string) $key,
                'label' => (string) $label,
            ];
        })
        ->values()
        ->toArray();

    $btnId = $id ?: ($name . '_button');
@endphp

<div class="mb-2" x-data="{
    open: false,
    search: '',
    value: @js($selectedValue),
    options: @js($formattedOptions),
    highlightedIndex: 0,
    autoSubmit: {{ $autoSubmit ? 'true' : 'false' }},

    init() {
        if (this.options.length === 0 && this.$refs.nativeSelect) {
            this.options = Array.from(this.$refs.nativeSelect.options)
                .filter(o => o.value !== '')
                .map(o => ({ value: o.value, label: o.textContent.trim() }));
        }
    },

    get selectedLabel() {
        let found = this.options.find(opt => String(opt.value) === String(this.value));
        return found ? found.label : '';
    },

    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(opt =>
            opt.label.toLowerCase().includes(this.search.toLowerCase())
        );
    },

    setOptions(newOpts) {
        this.options = newOpts;
        if (this.value && !newOpts.some(o => String(o.value) === String(this.value))) {
            this.value = '';
        }
    },

    getOptions() {
        var el = this.$refs.nativeSelect;
        if (!el) return [];
        return Array.from(el.options)
            .filter(o => o.value !== '')
            .map(o => ({ value: o.value, label: o.textContent.trim() }));
    },

    selectOption(val) {
        this.value = val;
        this.open = false;
        this.search = '';
        this.$nextTick(() => {
            const input = this.$refs.hiddenInput;
            if (input) {
                input.value = val;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (this.autoSubmit) {
                this.$el.closest('form')?.submit();
            }
        });
    },

    highlightNext() {
        if (this.highlightedIndex < this.filteredOptions.length - 1) {
            this.highlightedIndex++;
        }
    },

    highlightPrev() {
        if (this.highlightedIndex > 0) {
            this.highlightedIndex--;
        }
    },

    selectHighlighted() {
        if (this.filteredOptions.length > 0 && this.filteredOptions[this.highlightedIndex]) {
            this.selectOption(this.filteredOptions[this.highlightedIndex].value);
        }
    }
}" @click.outside="open = false" @keydown.escape.window="open = false"
    @options-updated.window="
        if ($event.detail && $event.detail.name === '{{ $name }}') {
            setOptions($event.detail.options);
        }
    "
    @set-value.window="
        if ($event.detail && $event.detail.name === '{{ $name }}') {
            value = $event.detail.value;
        }
    ">

    @if($label)
        <label for="{{ $btnId }}"
            class="block text-xs font-medium text-slate-600 mb-1">{!! $label !!}</label>
    @endif

    <input type="hidden" x-ref="hiddenInput" name="{{ $name }}" :value="value"
        @if($id) id="{{ $id }}" @endif {{ $required ? 'required' : '' }}>

    <select class="hidden" x-ref="nativeSelect" tabindex="-1">
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    <div class="relative">
        <button type="button" id="{{ $btnId }}"
            @click="open = !open; if(open) $nextTick(() => $refs.searchInput?.focus())"
            @keydown.arrow-down.prevent="if(!open) { open = true; } else { highlightNext(); }"
            @keydown.arrow-up.prevent="if(open) highlightPrev();"
            @keydown.enter.prevent="if(open) selectHighlighted();"
            {{ $attributes->merge([
                'class' => 'w-full rounded-md border border-slate-300 text-sm text-left flex items-center justify-between px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors bg-white cursor-pointer select-none',
            ]) }}>
            <span x-text="selectedLabel || '{{ addslashes($placeholder) }}'"
                :class="selectedLabel ? 'text-slate-800' : 'text-slate-400'" class="truncate"></span>
            <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2" :class="{ 'rotate-180': open }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute left-0 right-0 z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg overflow-hidden flex flex-col py-1 max-h-60">

            @if($searchable)
                <div class="p-1.5 border-b border-slate-100 sticky top-0 z-10">
                    <div class="relative">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-ref="searchInput" x-model="search"
                            @keydown.arrow-down.prevent="highlightNext()" @keydown.arrow-up.prevent="highlightPrev()"
                            @keydown.enter.prevent="selectHighlighted()" placeholder="Cari..."
                            class="w-full bg-white text-slate-800 border border-slate-200 text-sm rounded-md pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
            @endif

            <ul class="overflow-y-auto divide-y divide-slate-50 flex-1">
                <template x-for="(opt, index) in filteredOptions" :key="opt.value">
                    <li @click="selectOption(opt.value)" @mouseenter="highlightedIndex = index"
                        :class="{
                            'bg-blue-50 text-blue-700 font-medium': String(opt.value) === String(value),
                            'bg-slate-100 text-slate-900': index === highlightedIndex && String(opt.value) !== String(value),
                            'text-slate-700 hover:bg-slate-50': String(opt.value) !== String(value) && index !== highlightedIndex
                        }"
                        class="px-3 py-1.5 text-sm cursor-pointer flex items-center justify-between transition select-none">
                        <span x-text="opt.label" class="truncate"></span>
                        <template x-if="String(opt.value) === String(value)">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </li>
                </template>
                <template x-if="filteredOptions.length === 0">
                    <li class="px-3 py-3 text-sm text-center text-slate-400 select-none">Tidak ditemukan</li>
                </template>
            </ul>
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
