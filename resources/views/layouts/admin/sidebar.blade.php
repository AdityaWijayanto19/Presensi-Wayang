<aside x-data="{ sidebarOpen: false }"
       class="fixed inset-y-0 left-0 z-40 w-56 bg-slate-900 text-slate-300 flex flex-col lg:translate-x-0 transition-transform duration-200"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div x-show="sidebarOpen" x-cloak
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"
         @click="sidebarOpen = false">
    </div>

    <div class="flex items-center h-12 px-3 border-b border-slate-800">
        <a href="/panel/dashboard" class="flex items-center">
            <img src="{{ asset('assets/img/login/logo_aplikasi_admin_nyamping.png') }}" alt="Logo" class="h-6">
        </a>
    </div>

    <div class="lg:hidden border-b border-slate-800 px-3 py-2">
        <x-admin.dropdown align="left" class="w-full">
            <x-slot:trigger>
                <button class="flex items-center gap-2 w-full text-left">
                    <span class="w-7 h-7 rounded bg-slate-700 flex items-center justify-center text-white">
                        <i data-lucide="user-cog" style="width:14px;height:14px;"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium text-white truncate">{{ Auth::guard('user')->user()->name }}</div>
                        <div class="text-[10px] text-slate-400">
                            @if(Auth::guard('user')->user()->hasRole('super_admin'))
                                Super Admin
                            @elseif(Auth::guard('user')->user()->hasRole('admin'))
                                Admin
                            @else
                                Owner
                            @endif
                        </div>
                    </div>
                    <svg class="w-3 h-3 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </x-slot:trigger>

            <a href="/panel/settings" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">Pengaturan</a>
            <div class="border-t border-slate-100 my-0.5"></div>
            <a href="/proseslogoutadmin" class="block px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">Logout</a>
        </x-admin.dropdown>
    </div>

    <nav class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">

        {{-- Dashboard --}}
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/dashboard') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/dashboard">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
            </span>
            <span>Dashboard</span>
        </a>

        {{-- Data Master --}}
        @canany(['karyawan-view', 'unit-view'])
        <div x-data="{ open: {{ request()->is(['panel/karyawan', 'panel/unit']) ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="flex items-center gap-2 w-full px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is(['panel/karyawan', 'panel/unit']) ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <span class="flex-shrink-0 w-4 h-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
                </span>
                <span class="flex-1 text-left">Data Master</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-collapse>
                <div class="ml-4 mt-0.5 space-y-0.5 border-l border-slate-700 pl-2">
                    @can('karyawan-view')
                    <a class="flex items-center gap-1.5 px-2.5 py-1.5 rounded text-xs transition-colors {{ request()->is('panel/karyawan') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                       href="/panel/karyawan">Karyawan</a>
                    @endcan
                    @can('unit-view')
                    <a class="flex items-center gap-1.5 px-2.5 py-1.5 rounded text-xs transition-colors {{ request()->is('panel/unit') ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}"
                       href="/panel/unit">Unit Perusahaan</a>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany

        {{-- Monitoring --}}
        @can('monitoring-view')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/monitoring') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/monitoring">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1l0 -10"/><path d="M7 20h10"/><path d="M9 16v4"/><path d="M15 16v4"/><path d="M9 12v-4"/><path d="M12 12v-1"/><path d="M15 12v-2"/><path d="M12 12v-1"/></svg>
            </span>
            <span>Monitoring Presensi</span>
        </a>
        @endcan

         {{-- Laporan --}}
        @can('laporan-view')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/laporan') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/laporan">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4"/><path d="M6 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7"/><path d="M3 15l3 -3l3 3"/></svg>
            </span>
            <span>Laporan Presensi</span>
        </a>
        @endcan

        {{-- Data Izin --}}
        @can('izin-view')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/izin') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/izin">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-14a2 2 0 1 1 2 2h-2m2 -4h-11a3 3 0 0 0 -3 3v11"/><path d="M9 7l4 0"/><path d="M9 11l4 0"/></svg>
            </span>
            <span>Data Izin Karyawan</span>
        </a>
        @endcan

        {{-- Data Lembur --}}
        @can('lembur-view')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/lembur') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/lembur">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 7v5l3 3"/><path d="M12 21a9 9 0 1 0 0 -18a9 9 0 0 0 0 18"/></svg>
            </span>
            <span>Data Lembur Karyawan</span>
        </a>
        @endcan

        {{-- Data WFH --}}
        @can('wfh-view')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/wfh') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/wfh">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h3v-6h4v6h3a2 2 0 0 0 2 -2v-7"/></svg>
            </span>
            <span>Data WFH Karyawan</span>
            @php
                $totalPending = ($pendingWfhAdminCount ?? 0) + ($pendingLaporanAdminCount ?? 0);
            @endphp
            <span id="adminWfhBadge" class="ml-auto inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded bg-red-500 text-white text-[10px] font-bold"
                  style="{{ $totalPending > 0 ? '' : 'display:none;' }}">{{ $totalPending }}</span>
        </a>
        @endcan

        {{-- Users --}}
        @can('user-manage')
        <a class="flex items-center gap-2 px-2.5 py-2 rounded text-xs font-medium transition-colors {{ request()->is('panel/users') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
           href="/panel/users">
            <span class="flex-shrink-0 w-4 h-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.348 0 .686 .045 1.008 .128"/><path d="M19 16v3"/><path d="M19 22v.01"/></svg>
            </span>
            <span>Pengguna Administrator</span>
        </a>
        @endcan

    </nav>

</aside>

<div class="fixed top-3 left-3 z-50 lg:hidden" x-data="{ sidebarOpen: false }"
     x-on:toggle-sidebar.window="sidebarOpen = !sidebarOpen; $dispatch('toggle-sidebar')">
    <button @click="$dispatch('toggle-sidebar')"
            class="inline-flex items-center justify-center w-8 h-8 rounded bg-white shadow-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>
