@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Dashboard Administrator')

<x-admin.page-body>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-1.5">

        {{-- JUMLAH KARYAWAN --}}
        <a href="/panel/karyawan" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-cyan-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $jmlkaryawan }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Karyawan</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

        {{-- HADIR HARI INI --}}
        <a href="/panel/monitoring" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-green-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4"/><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekappresensi->jmlhadir ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Hadir Hari Ini</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

        {{-- WFH HARI INI --}}
        <a href="/panel/wfh" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-blue-600 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1 -1v-7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekapwfh->jmlwfh ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">WFH Hari Ini</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

        {{-- IZIN / SAKIT --}}
        <a href="/panel/izin?tanggal={{ date('Y-m-d') }}" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-yellow-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4"/><path d="M6 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekapizin->jmlizin ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Izin / Sakit</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

        {{-- TERLAMBAT --}}
        <a href="/panel/monitoring" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-red-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 7v5l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekappresensi->jmltelat ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Terlambat</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

        {{-- LEMBUR --}}
        <a href="/panel/lembur" class="no-underline">
            <x-admin.card hover>
                <div class="p-2.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-orange-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21v-2a4 4 0 0 0-4-4h-4a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekaplembur->jmllembur ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Lembur</div>
                        </div>
                    </div>
                </div>
            </x-admin.card>
        </a>

    </div>

    {{-- MENUNGGU PERSETUJUAN --}}
    @php
        $totalPending = ($pendingWfhAdmin ?? 0) + ($pendingLaporanAdmin ?? 0);
    @endphp
    @if ($totalPending > 0)
        <div class="mt-3">
            <a href="/panel/wfh" class="no-underline block">
                <x-admin.card>
                    <div class="p-2.5">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded bg-amber-100 border border-amber-200 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M12 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M17 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M15 17a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M5 17a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M8 7a4 4 0 0 1 8 0"/><path d="M8 17a4 4 0 0 0 8 0"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-slate-800">Menunggu Persetujuan</div>
                                <div class="text-[11px] text-slate-500">
                                    @if ($pendingWfhAdmin > 0)
                                        {{ $pendingWfhAdmin }} WFH
                                    @endif
                                    @if ($pendingWfhAdmin > 0 && $pendingLaporanAdmin > 0)
                                        &middot;
                                    @endif
                                    @if ($pendingLaporanAdmin > 0)
                                        {{ $pendingLaporanAdmin }} Laporan
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded bg-red-500 text-white text-[11px] font-bold">{{ $totalPending }}</span>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            </a>
        </div>
    @endif

    {{-- AKSES CEPAT --}}
    @php
        $quickLinks = [
            ['label' => 'Monitoring', 'url' => '/panel/monitoring', 'color' => 'bg-slate-100 text-slate-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1l0 -10"/><path d="M7 20h10"/><path d="M9 16v4"/><path d="M15 16v4"/>'],
            ['label' => 'Data WFH', 'url' => '/panel/wfh', 'color' => 'bg-blue-50 text-blue-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1 -1v-7"/>'],
            ['label' => 'Data Izin', 'url' => '/panel/izin', 'color' => 'bg-yellow-50 text-yellow-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4"/><path d="M6 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7"/>'],
            ['label' => 'Laporan', 'url' => '/panel/laporan', 'color' => 'bg-emerald-50 text-emerald-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 3v4a1 1 0 0 0 1 1h4"/><path d="M6 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-7"/><path d="M3 15l3 -3l3 3"/>'],
            ['label' => 'Karyawan', 'url' => '/panel/karyawan', 'color' => 'bg-cyan-50 text-cyan-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>'],
            ['label' => 'Lembur', 'url' => '/panel/lembur', 'color' => 'bg-orange-50 text-orange-600', 'icon' => '<path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21v-2a4 4 0 0 0-4-4h-4a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>'],
        ];
    @endphp
    <div class="mt-3">
        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Akses Cepat</div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5">
            @foreach ($quickLinks as $link)
                <a href="{{ $link['url'] }}" class="no-underline">
                    <x-admin.card hover>
                        <div class="p-2.5 text-center">
                            <div class="w-9 h-9 rounded {{ $link['color'] }} flex items-center justify-center mx-auto mb-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $link['icon'] !!}</svg>
                            </div>
                            <div class="text-[11px] font-medium text-slate-700 leading-tight">{{ $link['label'] }}</div>
                        </div>
                    </x-admin.card>
                </a>
            @endforeach
        </div>
    </div>

</x-admin.page-body>

@endsection
