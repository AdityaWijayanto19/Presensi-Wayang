<header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-3 py-1.5 hidden lg:block print:hidden">
    <div class="flex items-center justify-between">

        @if (trim($__env->yieldContent('page_title')) !== '')
            <div class="min-w-0">
                <h2 class="text-sm font-semibold text-slate-800 leading-tight truncate">{{ $__env->yieldContent('page_title') }}</h2>
            </div>
        @else
            <div></div>
        @endif

        <x-admin.dropdown align="right" class="ml-3 shrink-0">
            <x-slot:trigger>
                <button class="flex items-center gap-2 text-left">
                    <span class="w-7 h-7 rounded bg-slate-200 flex items-center justify-center text-slate-600">
                        <i data-lucide="user-cog" style="width:14px;height:14px;"></i>
                    </span>
                    <div class="hidden xl:block">
                        <div class="text-xs font-medium text-slate-700">{{ Auth::guard('user')->user()->name }}</div>
                        <div class="text-[10px] text-slate-400">Administrator</div>
                    </div>
                    <svg class="w-3 h-3 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </x-slot:trigger>

            <a href="/panel/settings" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100 transition-colors">Pengaturan</a>
            <div class="border-t border-slate-100 my-0.5"></div>
            <a href="/proseslogoutadmin" id="logout-admin" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100 transition-colors">Logout</a>
        </x-admin.dropdown>

    </div>
</header>
