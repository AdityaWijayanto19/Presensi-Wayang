@props(['label' => null, 'error' => null, 'placeholder' => null])

<div class="mb-2" x-data="{ open: false }">
    @if($label)
        <label class="block text-xs font-medium text-slate-600 mb-1">{!! $label !!}</label>
    @endif

    <div class="relative">
        <select {{ $attributes->merge(['class' => 'w-full appearance-none rounded-md border border-slate-300 px-3 pr-9 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors bg-white cursor-pointer']) }}
            @focus="open = true" @blur="open = false">
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 transition-transform duration-200"
              :class="open ? 'rotate-180' : ''">
            <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
        </span>
    </div>

    @if($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
