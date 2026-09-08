@extends('layouts.admin.app')

@section('content')

<x-app.page-header title="Dashboard Administrator" pretitle="WAG - Presensi Digital" />

<x-app.page-body>

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2">

        {{-- JUMLAH KARYAWAN --}}
        <a href="/panel/karyawan" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-cyan-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $jmlkaryawan }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Karyawan</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

        {{-- KARYAWAN HADIR --}}
        <a href="/panel/monitoring" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-green-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3"/><path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6"/><path d="M12 11v2a14 14 0 0 0 2.5 8"/><path d="M8 15a18 18 0 0 0 1.8 6"/><path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekappresensi->jmlhadir ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Hadir Hari Ini</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

        {{-- KARYAWAN WFH --}}
        <a href="/panel/wfh" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-blue-600 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a1 1 0 0 0 1 1h3v-6h6v6h3a1 1 0 0 0 1 -1v-7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekapwfh->jmlwfh ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">WFH Hari Ini</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

        {{-- KARYAWAN IZIN/SAKIT --}}
        <a href="/panel/izin?tanggal={{ date('Y-m-d') }}" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-yellow-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.18 8.189a4.01 4.01 0 0 0 2.616 2.627m3.507 -.545a4 4 0 1 0 -5.59 -5.552"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.412 0 .81 .062 1.183 .178m2.633 2.618c.12 .38 .184 .785 .184 1.204v2"/><path d="M3 3l18 18"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekapizin->jmlizin ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Izin/Sakit</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

        {{-- KARYAWAN TERLAMBAT --}}
        <a href="/panel/monitoring" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-red-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekappresensi->jmltelat ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Terlambat</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

        {{-- KARYAWAN LEMBUR --}}
        <a href="/panel/lembur" class="no-underline">
            <x-app.card hover>
                <div class="p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex-shrink-0 w-8 h-8 rounded bg-orange-500 text-white flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 7v5l3 3"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-bold text-slate-800 leading-tight">{{ $rekaplembur->jmllembur ?? 0 }}</div>
                            <div class="text-[11px] text-slate-500 truncate">Lembur</div>
                        </div>
                    </div>
                </div>
            </x-app.card>
        </a>

    </div>

</x-app.page-body>

@endsection
