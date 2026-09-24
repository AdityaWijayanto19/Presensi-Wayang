@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Pengajuan</div>
    </div>
@endsection

@section('content')
    <div class="section mt-[70px]">
        <h3 class="text-[15px] font-bold text-[#1c1917] mb-3">Ajukan Permohonan</h3>

        {{-- Izin --}}
        <a href="/izin"
            class="flex items-center gap-4 bg-white rounded-2xl p-4 mb-3 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="text-amber-600" style="width:22px;height:22px;"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[14px] font-bold text-[#1c1917]">Izin</div>
                <div class="text-[11px] text-[#78716c] mt-0.5">Ajukan izin tidak masuk, terlambat, atau pulang cepat</div>
            </div>
            <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0" style="width:18px;height:18px;"></i>
        </a>

        {{-- Lembur --}}
        <a href="/lembur"
            class="flex items-center gap-4 bg-white rounded-2xl p-4 mb-3 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform">
            <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-200 flex items-center justify-center shrink-0">
                <i data-lucide="timer" class="text-violet-600" style="width:22px;height:22px;"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[14px] font-bold text-[#1c1917]">Lembur</div>
                <div class="text-[11px] text-[#78716c] mt-0.5">Ajukan permohonan lembur dan upload laporan</div>
            </div>
            <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0" style="width:18px;height:18px;"></i>
        </a>

        {{-- WFH --}}
        <a href="/wfh"
            class="flex items-center gap-4 bg-white rounded-2xl p-4 mb-3 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform">
            <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0">
                <i data-lucide="home" class="text-sky-600" style="width:22px;height:22px;"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[14px] font-bold text-[#1c1917]">Work From Home</div>
                <div class="text-[11px] text-[#78716c] mt-0.5">Ajukan WFH dan upload laporan kerja</div>
            </div>
            <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0" style="width:18px;height:18px;"></i>
        </a>

        {{-- Cuti Tahunan --}}
        <a href="/cuti"
            class="flex items-center gap-4 bg-white rounded-2xl p-4 mb-3 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                <i data-lucide="palmtree" class="text-emerald-600" style="width:22px;height:22px;"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[14px] font-bold text-[#1c1917]">Cuti Tahunan</div>
                <div class="text-[11px] text-[#78716c] mt-0.5">Upload file cuti yang sudah ditandatangani</div>
            </div>
            <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0" style="width:18px;height:18px;"></i>
        </a>
    </div>
@endsection
