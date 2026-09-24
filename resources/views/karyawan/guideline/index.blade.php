@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/settings" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Panduan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="flex mt-[70px]">
        <div class="w-full px-3">

            {{-- Hero --}}
            <div class="bg-white rounded-2xl p-4 mb-3 shadow-[0_1px_3px_rgba(0,0,0,0.06)]">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-coklat/10 border border-[#f0ece8] flex items-center justify-center text-coklat shrink-0">
                        <i data-lucide="book-open" style="width:22px;height:22px;"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[15px] font-bold text-[#1c1917] leading-tight">Panduan & Ketentuan</div>
                        <div class="text-[12px] text-[#78716c] mt-0.5">Pahami aturan pengajuan pada WFH, Lembur, Izin, dan Cuti.</div>
                    </div>
                </div>
            </div>

            <h3 class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase mb-2">Pilih Topik</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- WFH (aktif) --}}
                <a href="/guideline/wfh"
                    class="group flex items-start gap-3.5 bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform border border-transparent hover:border-sky-200">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0 text-sky-600">
                        <i data-lucide="home" style="width:22px;height:22px;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[14px] font-bold text-[#1c1917]">Work From Home</span>
                        </div>
                        <div class="text-[11.5px] text-[#78716c] mt-1 leading-relaxed">Cara ajukan, aturan jam, persetujuan, laporan, dan status Unpaid.</div>
                    </div>
                    <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0 mt-1 group-hover:text-sky-500 transition-colors" style="width:18px;height:18px;"></i>
                </a>

                {{-- Lembur (aktif) --}}
                <a href="/guideline/lembur"
                    class="group flex items-start gap-3.5 bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform border border-transparent hover:border-violet-200">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 border border-violet-200 flex items-center justify-center shrink-0 text-violet-600">
                        <i data-lucide="timer" style="width:22px;height:22px;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[14px] font-bold text-[#1c1917]">Lembur</span>
                        </div>
                        <div class="text-[11.5px] text-[#78716c] mt-1 leading-relaxed">Aturan jam, durasi & prorate, persetujuan, foto, dan laporan.</div>
                    </div>
                    <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0 mt-1 group-hover:text-violet-500 transition-colors" style="width:18px;height:18px;"></i>
                </a>

                {{-- Izin (aktif) --}}
                <a href="/guideline/izin"
                    class="group flex items-start gap-3.5 bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform border border-transparent hover:border-amber-200">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0 text-amber-600">
                        <i data-lucide="file-text" style="width:22px;height:22px;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[14px] font-bold text-[#1c1917]">Izin</span>
                        </div>
                        <div class="text-[11.5px] text-[#78716c] mt-1 leading-relaxed">5 jenis izin, aturan jam, persetujuan, dan efek ke absen pulang.</div>
                    </div>
                    <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0 mt-1 group-hover:text-amber-500 transition-colors" style="width:18px;height:18px;"></i>
                </a>

                {{-- Cuti (aktif) --}}
                <a href="/guideline/cuti"
                    class="group flex items-start gap-3.5 bg-white rounded-2xl p-4 shadow-[0_1px_3px_rgba(0,0,0,0.06)] no-underline active:scale-[0.98] transition-transform border border-transparent hover:border-emerald-200">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0 text-emerald-600">
                        <i data-lucide="palmtree" style="width:22px;height:22px;"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[14px] font-bold text-[#1c1917]">Cuti</span>
                        </div>
                        <div class="text-[11.5px] text-[#78716c] mt-1 leading-relaxed">Kuota, aturan upload, tanggal & dokumen, serta efek ke laporan.</div>
                    </div>
                    <i data-lucide="chevron-right" class="text-[#a8a29e] shrink-0 mt-1 group-hover:text-emerald-500 transition-colors" style="width:18px;height:18px;"></i>
                </a>
            </div>
        </div>
    </div>
@endsection
