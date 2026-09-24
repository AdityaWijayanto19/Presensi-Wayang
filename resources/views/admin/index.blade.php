@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Dashboard Administrator')

<x-admin.page-body>

    @php
        $totalPending = ($pendingWfhAdmin ?? 0) + ($pendingLaporanAdmin ?? 0)
            + ($pendingLemburAdmin ?? 0) + ($pendingLaporanLemburAdmin ?? 0)
            + ($pendingIzinAdmin ?? 0);
        $persenHadir = $jmlkaryawan > 0 ? round((int) ($rekappresensi->jmlhadir ?? 0) / $jmlkaryawan * 100) : 0;
        $inisial = function ($nama) {
            $parts = preg_split('/\s+/', trim((string) $nama));
            $a = strtoupper(substr($parts[0] ?? '', 0, 1));
            $b = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';

            return trim($a.$b) ?: '?';
        };
    @endphp

    {{-- PAGE HEADER --}}
    <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2 pb-4 mb-4 border-b border-slate-200">
        <div>
            <h1 class="text-[17px] font-semibold text-slate-900 leading-tight tracking-tight">
                Selamat {{ $sapaan }}, {{ Auth::guard('user')->user()->name }}</h1>
            <p class="text-[13px] text-slate-500 mt-0.5">{{ $tanggalIndo }}</p>
        </div>
        <div class="flex items-center gap-4 text-[13px] text-slate-500">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span class="font-medium text-slate-700 tabular-nums">{{ $rekappresensi->jmlhadir ?? 0 }}</span>
                hadir
                <span class="text-slate-400">({{ $persenHadir }}%)</span>
            </span>
            <span class="w-px h-3.5 bg-slate-200"></span>
            <span>
                <span class="font-medium text-slate-700 tabular-nums">{{ $totalPending }}</span> antrean
            </span>
            <span class="w-px h-3.5 bg-slate-200"></span>
            <span class="tabular-nums text-slate-600">
                <span id="jamLive">--:--:--</span>
                <span class="text-slate-400">WIB</span>
            </span>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-2">

        @php
            $statCards = [
                [
                    'label' => 'Karyawan',
                    'value' => $jmlkaryawan,
                    'meta' => 'Terdaftar',
                    'url' => '/panel/karyawan',
                    'icon' => 'users',
                    'accent' => 'bg-cyan-500',
                ],
                [
                    'label' => 'Hadir Hari Ini',
                    'value' => $rekappresensi->jmlhadir ?? 0,
                    'meta' => $persenHadir . '% dari total',
                    'url' => '/panel/monitoring',
                    'icon' => 'circle-check',
                    'accent' => 'bg-emerald-500',
                ],
                [
                    'label' => 'WFH Hari Ini',
                    'value' => $rekapwfh->jmlwfh ?? 0,
                    'meta' => 'Work from home',
                    'url' => '/panel/wfh',
                    'icon' => 'home',
                    'accent' => 'bg-blue-500',
                ],
                [
                    'label' => 'Cuti Hari Ini',
                    'value' => $rekapcuti->jmlcuti ?? 0,
                    'meta' => 'Sedang cuti',
                    'url' => '/panel/cuti',
                    'icon' => 'palmtree',
                    'accent' => 'bg-violet-500',
                ],
                [
                    'label' => 'Izin / Sakit',
                    'value' => $rekapizin->jmlizin ?? 0,
                    'meta' => 'Izin & sakit',
                    'url' => '/panel/izin?tanggal=' . date('Y-m-d'),
                    'icon' => 'file-text',
                    'accent' => 'bg-amber-500',
                ],
                [
                    'label' => 'Terlambat',
                    'value' => $rekappresensi->jmltelat ?? 0,
                    'meta' => 'Di atas jam masuk',
                    'url' => '/panel/monitoring',
                    'icon' => 'clock',
                    'accent' => 'bg-rose-500',
                ],
                [
                    'label' => 'Lembur',
                    'value' => $rekaplembur->jmllembur ?? 0,
                    'meta' => 'Hari ini',
                    'url' => '/panel/lembur',
                    'icon' => 'briefcase',
                    'accent' => 'bg-orange-500',
                ],
            ];
        @endphp

        @foreach ($statCards as $card)
            <a href="{{ $card['url'] }}" class="no-underline block h-full">
                <x-admin.card hover class="h-full overflow-hidden">
                    <div class="h-[3px] {{ $card['accent'] }}"></div>
                    <div class="px-3.5 py-3 flex flex-col h-full min-h-[92px]">
                        <div class="flex items-start justify-between gap-2">
                            <span
                                class="text-[10px] font-semibold uppercase tracking-[0.06em] text-slate-400 leading-tight">{{ $card['label'] }}</span>
                            <i data-lucide="{{ $card['icon'] }}" style="width:14px;height:14px;"
                                class="text-slate-300 flex-shrink-0 -mt-0.5"></i>
                        </div>
                        <div class="mt-auto pt-3">
                            <div class="text-[26px] font-semibold text-slate-900 leading-none tabular-nums">
                                {{ $card['value'] }}</div>
                            <div class="text-[11px] text-slate-400 mt-1.5 leading-tight">{{ $card['meta'] }}</div>
                        </div>
                    </div>
                </x-admin.card>
            </a>
        @endforeach

    </div>

    {{-- MENUNGGU PERSETUJUAN --}}
    <div class="mt-4">
        <div
            class="flex flex-wrap items-center gap-x-5 gap-y-2 px-3.5 py-2.5 bg-white border border-slate-200 rounded-lg">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $totalPending > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                <span class="text-[13px] font-medium text-slate-700">
                    {{ $totalPending > 0 ? 'Menunggu Persetujuan' : 'Semua Antrean Beres' }}
                </span>
                <span class="text-[12px] text-slate-400 hidden sm:inline">
                    {{ $totalPending > 0 ? '· Proses agar tidak tertunda' : '· Tidak ada pengajuan menunggu HR' }}
                </span>
            </div>

            @if ($totalPending > 0)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 ml-auto text-[12px]">
                    @if ($pendingIzinAdmin > 0)
                        <a href="/panel/izin" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 no-underline">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Izin <span class="font-semibold text-slate-900 tabular-nums">{{ $pendingIzinAdmin }}</span>
                        </a>
                    @endif
                    @if ($pendingWfhAdmin > 0)
                        <a href="/panel/wfh" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 no-underline">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            WFH <span class="font-semibold text-slate-900 tabular-nums">{{ $pendingWfhAdmin }}</span>
                        </a>
                    @endif
                    @if ($pendingLaporanAdmin > 0)
                        <a href="/panel/wfh" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 no-underline">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            Laporan WFH <span class="font-semibold text-slate-900 tabular-nums">{{ $pendingLaporanAdmin }}</span>
                        </a>
                    @endif
                    @if ($pendingLemburAdmin > 0)
                        <a href="/panel/lembur" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 no-underline">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            Lembur <span class="font-semibold text-slate-900 tabular-nums">{{ $pendingLemburAdmin }}</span>
                        </a>
                    @endif
                    @if ($pendingLaporanLemburAdmin > 0)
                        <a href="/panel/lembur" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 no-underline">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                            Laporan Lembur <span class="font-semibold text-slate-900 tabular-nums">{{ $pendingLaporanLemburAdmin }}</span>
                        </a>
                    @endif
                    <span
                        class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded bg-slate-900 text-white text-[11px] font-semibold tabular-nums">{{ $totalPending }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- CHART ROW 1: Kehadiran 7 hari + Penggunaan cuti --}}
    <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-3">

        <x-admin.card class="lg:col-span-2">
            <div class="p-3">
                <div class="flex items-start justify-between mb-2 gap-2">
                    <div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Kehadiran 7 Hari
                            Terakhir</div>
                        <div class="text-xs text-slate-500">Tren jumlah hadir vs terlambat setiap hari</div>
                    </div>
                </div>
                <div class="h-56">
                    <canvas id="chartHadir7"></canvas>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="p-3">
                <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Penggunaan Cuti
                    {{ now('Asia/Jakarta')->year }}</div>
                <div class="text-xs text-slate-500 mb-2">Terpakai vs sisa jatah seluruh karyawan</div>
                <div class="h-44">
                    <canvas id="chartCuti"></canvas>
                </div>
                <div class="mt-2 grid grid-cols-3 gap-1 text-center">
                    <div class="rounded bg-purple-50 py-1.5">
                        <div class="text-sm font-bold text-purple-600">{{ $cutiTerpakaiTahun }}</div>
                        <div class="text-[10px] text-slate-400">Terpakai</div>
                    </div>
                    <div class="rounded bg-slate-100 py-1.5">
                        <div class="text-sm font-bold text-slate-600">{{ $cutiSisaTahun }}</div>
                        <div class="text-[10px] text-slate-400">Sisa</div>
                    </div>
                    <div class="rounded bg-slate-800 py-1.5">
                        <div class="text-sm font-bold text-white">{{ $totalJatahCuti }}</div>
                        <div class="text-[10px] text-slate-300">Total Jatah</div>
                    </div>
                </div>
            </div>
        </x-admin.card>

    </div>

    {{-- CHART ROW 2: Karyawan per unit + Status pengajuan bulan ini --}}
    <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-3">

        <x-admin.card>
            <div class="p-3">
                <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Karyawan per Unit</div>
                <div class="text-xs text-slate-500 mb-2">Sebaran headcount tiap unit perusahaan</div>
                <div class="h-56">
                    <canvas id="chartUnit"></canvas>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <div class="p-3">
                <div class="flex items-start justify-between mb-2 gap-2">
                    <div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Status Pengajuan
                            Bulan Ini</div>
                        <div class="text-xs text-slate-500">Perbandingan disetujui / menunggu / ditolak</div>
                    </div>
                    <div class="hidden sm:flex items-center gap-1.5 text-[11px]">
                        <span class="inline-flex items-center gap-1 text-slate-500">
                            <span class="w-2 h-2 rounded-sm bg-emerald-500"></span> Disetujui
                        </span>
                        <span class="inline-flex items-center gap-1 text-slate-500">
                            <span class="w-2 h-2 rounded-sm bg-amber-500"></span> Menunggu
                        </span>
                        <span class="inline-flex items-center gap-1 text-slate-500">
                            <span class="w-2 h-2 rounded-sm bg-rose-500"></span> Ditolak
                        </span>
                    </div>
                </div>
                <div class="h-56">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </x-admin.card>

    </div>

    {{-- BOTTOM ROW: Cuti hari ini + Pengajuan terbaru --}}
    <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-3">

        {{-- CUTI HARI INI --}}
        <x-admin.card>
            <div class="p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Cuti Hari Ini</div>
                    <a href="/panel/cuti" class="text-[11px] text-blue-600 hover:underline no-underline">Lihat
                        semua</a>
                </div>
                @forelse ($cutiHariIniList as $cuti)
                    <div class="flex items-center gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <div
                            class="flex-shrink-0 w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-[10px] font-bold flex items-center justify-center">
                            {{ $inisial(optional($cuti->karyawan)->nama_lengkap ?? $cuti->nik) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-slate-800 truncate">
                                {{ optional($cuti->karyawan)->nama_lengkap ?? $cuti->nik }}</div>
                            <div class="text-[10px] text-slate-400 truncate">
                                {{ optional(optional($cuti->karyawan)->unitperusahaan)->perusahaan ?? optional($cuti->karyawan)->unit ?? '-' }}
                                &middot; {{ $cuti->durasi_hari }} hari
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <i data-lucide="palmtree" style="width:24px;height:24px;"
                            class="text-slate-300 mx-auto mb-1.5"></i>
                        <div class="text-xs text-slate-400">Tidak ada karyawan cuti hari ini</div>
                    </div>
                @endforelse
            </div>
        </x-admin.card>

        {{-- PENGAJUAN TERBARU --}}
        <x-admin.card class="lg:col-span-2">
            <div class="p-3">
                <div class="flex items-center justify-between mb-1">
                    <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Pengajuan Terbaru
                    </div>
                    <span class="text-[11px] text-slate-400">Aktivitas terkini</span>
                </div>
                @forelse ($pengajuanTerbaru as $p)
                    <div class="flex items-center gap-2.5 py-2 border-b border-slate-100 last:border-0">
                        <div
                            class="flex-shrink-0 w-7 h-7 rounded {{ $p['color'] }} flex items-center justify-center">
                            <i data-lucide="{{ $p['icon'] }}" style="width:14px;height:14px;"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-slate-800 truncate">{{ $p['nama'] }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $p['jenis'] }} &middot;
                                {{ $p['detail'] }}</div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span
                                class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium {{ $p['badge_class'] }}">{{ $p['badge_label'] }}</span>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $p['waktu'] }}</div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Belum ada pengajuan masuk</div>
                @endforelse
            </div>
        </x-admin.card>

    </div>

</x-admin.page-body>

@endsection

@push('myscript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();

            // Live jam WIB
            function tikJam() {
                var el = document.getElementById('jamLive');
                if (!el) return;
                var d = new Date();
                var utc = d.getTime() + d.getTimezoneOffset() * 60000;
                var wib = new Date(utc + 3600000 * 7);
                var pad = function(n) { return ('0' + n).slice(-2); };
                el.textContent = pad(wib.getHours()) + ':' + pad(wib.getMinutes()) + ':' + pad(wib.getSeconds());
            }
            tikJam();
            setInterval(tikJam, 1000);

            if (typeof Chart === 'undefined') return;

            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.font.size = 11;
            Chart.defaults.color = '#64748b';

            var gridColor = '#f1f5f9';
            var legendOpts = {
                position: 'bottom',
                labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'rectRounded', padding: 12 }
            };

            // 1) Kehadiran 7 hari
            var elHadir = document.getElementById('chartHadir7');
            if (elHadir) {
                new Chart(elHadir, {
                    type: 'bar',
                    data: {
                        labels: @json($labels7),
                        datasets: [{
                                label: 'Hadir',
                                data: @json($hadir7),
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                                maxBarThickness: 28
                            },
                            {
                                label: 'Terlambat',
                                data: @json($telat7),
                                backgroundColor: '#f59e0b',
                                borderRadius: 4,
                                maxBarThickness: 28
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: legendOpts },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { precision: 0, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // 2) Penggunaan cuti
            var elCuti = document.getElementById('chartCuti');
            if (elCuti) {
                new Chart(elCuti, {
                    type: 'doughnut',
                    data: {
                        labels: ['Terpakai', 'Sisa'],
                        datasets: [{
                            data: [{{ $cutiTerpakaiTahun }}, {{ $cutiSisaTahun }}],
                            backgroundColor: ['#8b5cf6', '#e2e8f0'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: { legend: legendOpts }
                    }
                });
            }

            // 3) Karyawan per unit
            var elUnit = document.getElementById('chartUnit');
            if (elUnit) {
                var unitLabels = @json($unitChart->pluck('label'));
                var unitValues = @json($unitChart->pluck('jml'));
                new Chart(elUnit, {
                    type: 'bar',
                    data: {
                        labels: unitLabels,
                        datasets: [{
                            label: 'Karyawan',
                            data: unitValues,
                            backgroundColor: '#06b6d4',
                            borderRadius: 4,
                            maxBarThickness: 18
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { precision: 0, color: '#94a3b8' }
                            },
                            y: { grid: { display: false }, ticks: { color: '#64748b' } }
                        }
                    }
                });
            }

            // 4) Status pengajuan bulan ini
            var elStatus = document.getElementById('chartStatus');
            if (elStatus) {
                var sc = @json($statusChart);
                new Chart(elStatus, {
                    type: 'bar',
                    data: {
                        labels: ['WFH', 'Lembur', 'Izin'],
                        datasets: [{
                                label: 'Disetujui',
                                data: [sc.wfh.approved, sc.lembur.approved, sc.izin.approved],
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                                maxBarThickness: 32
                            },
                            {
                                label: 'Menunggu',
                                data: [sc.wfh.pending, sc.lembur.pending, sc.izin.pending],
                                backgroundColor: '#f59e0b',
                                borderRadius: 4,
                                maxBarThickness: 32
                            },
                            {
                                label: 'Ditolak',
                                data: [sc.wfh.rejected, sc.lembur.rejected, sc.izin.rejected],
                                backgroundColor: '#f43f5e',
                                borderRadius: 4,
                                maxBarThickness: 32
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { precision: 0, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
