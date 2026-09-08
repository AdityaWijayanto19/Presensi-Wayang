<header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-2 hidden lg:block print:hidden">
    <div class="max-w-7xl mx-auto flex items-center justify-between">

        <div></div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 text-left">
                <span class="w-7 h-7 rounded bg-slate-200 flex items-center justify-center text-xs font-medium text-slate-600 overflow-hidden"
                      style="background-image: url('{{ asset('assets/img/admin_icon.png') }}'); background-size: cover;">
                </span>
                <div class="hidden xl:block">
                    <div class="text-xs font-medium text-slate-700">{{ Auth::guard('user')->user()->name }}</div>
                    <div class="text-[10px] text-slate-400">Administrator</div>
                </div>
                <svg class="w-3 h-3 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="open = false"
                 class="absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-slate-200 py-1 z-50">
                <a href="/panel/settings" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100 transition-colors">Pengaturan</a>
                <div class="border-t border-slate-100 my-0.5"></div>
                <a href="/proseslogoutadmin" id="logout-admin" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100 transition-colors">Logout</a>
            </div>
        </div>
    </div>
</header>
