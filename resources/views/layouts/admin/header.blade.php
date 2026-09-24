<header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-3 py-1.5 hidden lg:block print:hidden">
    <div class="flex items-center justify-between">

        @if (trim($__env->yieldContent('page_title')) !== '')
            <div class="min-w-0">
                <h2 class="text-sm font-semibold text-slate-800 leading-tight truncate">
                    {{ $__env->yieldContent('page_title') }}</h2>
            </div>
        @else
            <div></div>
        @endif

       @php
    $adminUser = Auth::guard('user')->user();
    $roleLabel = $adminUser->hasRole('super_admin') ? 'Super Admin' : ($adminUser->hasRole('admin') ? 'Admin' : 'Owner');
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($adminUser->name, 0, 1));
@endphp

<x-admin.dropdown align="right" class="ml-3 shrink-0">
    <x-slot:trigger>
        <button type="button"
            class="group flex items-center gap-2.5 rounded-lg py-1 pl-1 pr-2 text-left transition-colors hover:bg-slate-100">
            <span
                class="flex h-8 w-8 items-center justify-center rounded-full bg-coklat text-xs font-semibold text-white">
                {{ $initial }}
            </span>

            <div class="hidden leading-tight xl:block">
                <div class="max-w-[140px] truncate text-[13px] font-medium text-slate-800">
                    {{ $adminUser->name }}
                </div>
                <div class="text-[11px] text-slate-400">{{ $roleLabel }}</div>
            </div>

            <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </x-slot:trigger>

    <div class="w-44 overflow-hidden rounded-xl p-1.5">
        <a href="/panel/settings"
            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
            <i data-lucide="settings" style="width:15px;height:15px;"></i>
            Pengaturan
        </a>

        <div class="my-1 h-px bg-slate-100"></div>

        <a href="/proseslogoutadmin" id="logout-admin"
            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600">
            <i data-lucide="log-out" style="width:15px;height:15px;"></i>
            Logout
        </a>
    </div>
</x-admin.dropdown>

    </div>
</header>
