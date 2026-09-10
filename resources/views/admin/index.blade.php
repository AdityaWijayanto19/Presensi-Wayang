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
                            <i data-lucide="users" style="width:16px;height:16px;"></i>
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
                            <i data-lucide="circle-check" style="width:16px;height:16px;"></i>
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
                            <i data-lucide="home" style="width:16px;height:16px;"></i>
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
                            <i data-lucide="file-text" style="width:16px;height:16px;"></i>
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
                            <i data-lucide="clock" style="width:16px;height:16px;"></i>
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
                            <i data-lucide="briefcase" style="width:16px;height:16px;"></i>
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
                                <i data-lucide="bell" style="width:18px;height:18px;" class="text-amber-600"></i>
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
            ['label' => 'Monitoring', 'url' => '/panel/monitoring', 'color' => 'bg-slate-100 text-slate-600', 'icon' => 'monitor'],
            ['label' => 'Data WFH', 'url' => '/panel/wfh', 'color' => 'bg-blue-50 text-blue-600', 'icon' => 'home'],
            ['label' => 'Data Izin', 'url' => '/panel/izin', 'color' => 'bg-yellow-50 text-yellow-600', 'icon' => 'file-text'],
            ['label' => 'Laporan', 'url' => '/panel/laporan', 'color' => 'bg-emerald-50 text-emerald-600', 'icon' => 'bar-chart-3'],
            ['label' => 'Karyawan', 'url' => '/panel/karyawan', 'color' => 'bg-cyan-50 text-cyan-600', 'icon' => 'users'],
            ['label' => 'Lembur', 'url' => '/panel/lembur', 'color' => 'bg-orange-50 text-orange-600', 'icon' => 'briefcase'],
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
                                <i data-lucide="{{ $link['icon'] }}" style="width:18px;height:18px;"></i>
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

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});
</script>
@endpush
