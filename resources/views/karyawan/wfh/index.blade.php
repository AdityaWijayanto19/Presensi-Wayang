@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
         <div class="left">
            <a href="/pengajuan" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Data Work From Home</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    @endphp

    @if ($datawfh->count() > 0)
        @php
            $approvedCount = $datawfh->where('status', 'approved')->count();
            $unpaidCount = $datawfh->where('status', 'unpaid')->count();
        @endphp
        <div class="flex mt-3">
            <div class="w-full px-3">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="check-check" class="text-emerald-600" style="width:13px;height:13px;"></i> {{ $datawfh->count() }} Data</span>
                        <span class="mx-1.5 text-[#e7e5e4]">•</span>
                        @if ($approvedCount > 0)
                            <span class="text-emerald-700">{{ $approvedCount }} Disetujui</span>
                        @endif
                        @if ($unpaidCount > 0)
                            @if ($approvedCount > 0)
                                <span class="mx-1.5 text-[#e7e5e4]">•</span>
                            @endif
                            <span class="text-gray-500">{{ $unpaidCount }} Unpaid</span>
                        @endif
                    </p>
                    <span class="text-[11px] font-medium text-[#78716c] bg-white border border-[#f0ece8] rounded-full px-2.5 py-1">{{ date('M Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Data WFH Clear — hanya tanggal, hari, status dari DB, file --}}
    <div class="flex mt-3">
        <div class="w-full px-3">
            @forelse ($datawfh as $d)
                @php
                    $ts = strtotime($d->tgl_wfh);
                    $weekday = $weekdayMap[date('l', $ts)] ?? date('l', $ts);
                    $displayDate = date('d M Y', $ts);
                    // Status dari DB (WfhStatus enum) — bukan statis
                    $status = $d->status instanceof \App\Enums\WfhStatus ? $d->status->value : ($d->status ?? 'approved');
                    $statusLabel = match($status){
                        'pending_atasan' => 'Menunggu Atasan',
                        'pending_admin' => 'Menunggu HR',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'unpaid' => 'Unpaid',
                        default => ucfirst($status)
                    };
                    $badgeClass = match($status){
                        'pending_atasan' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'pending_admin' => 'bg-sky-100 text-sky-700 border-sky-200',
                        'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
                        'unpaid' => 'bg-gray-100 text-gray-700 border-gray-200',
                        default => 'bg-gray-100 text-gray-700 border-gray-200'
                    };
                    $pdfUrl = !empty($d->pdf_form_path) ? \Illuminate\Support\Facades\Storage::url($d->pdf_form_path) : "#";
                    $pdfName = !empty($d->pdf_form_path) ? basename($d->pdf_form_path) : '';
                    $laporanUrl = !empty($d->laporan_file) ? \Illuminate\Support\Facades\Storage::url($d->laporan_file) : null;
                @endphp
                <div class="presensi-card mb-2.5">
                    <div class="flex items-start gap-3">
                        <div class="presensi-icon-box icon-wfh">
                            <i data-lucide="home"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[14.5px] font-bold text-[#1c1917] tracking-tight leading-none">{{ $displayDate }}</span>
                                <span class="presensi-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <i data-lucide="calendar" class="text-[#a8a29e]" style="width:12px;height:12px;"></i>
                                <span class="text-[12px] font-medium text-[#78716c]">{{ $weekday }}</span>
                            </div>
                            @if(($d->laporan_status && ($d->laporan_status instanceof \App\Enums\WfhStatus ? $d->laporan_status->value : $d->laporan_status) === 'rejected') && !empty($d->laporan_rejected_reason))
                                <div class="mt-1 text-[11px] text-rose-500">Alasan penolakan laporan: {{ $d->laporan_rejected_reason }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="presensi-divider"></div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if($pdfUrl !== "#")
                            <button type="button" class="file-pill js-preview" data-url="{{ $pdfUrl }}" data-filename="{{ $pdfName }}" data-label="Form WFH — {{ $displayDate }}">
                                <i data-lucide="file"></i> Form
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-[#f0f9ff] border border-[#bae6fd] text-[9px] font-bold text-[#0369a1] uppercase">{{ strtolower(pathinfo($pdfName, PATHINFO_EXTENSION)) }}</span>
                            </button>
                        @endif
                        @if($laporanUrl)
                            <button type="button" class="file-pill js-preview" data-url="{{ $laporanUrl }}" data-filename="{{ basename($laporanUrl) }}" data-label="Laporan WFH — {{ $displayDate }}">
                                <i data-lucide="clipboard"></i> Laporan
                            </button>
                        @elseif(!empty($d->laporan_deskripsi))
                            <button type="button" class="file-pill js-preview-laporan" data-deskripsi="{{ $d->laporan_deskripsi }}" data-tgl="{{ $displayDate }}" data-label="Laporan WFH — {{ $displayDate }}">
                                <i data-lucide="clipboard"></i> Laporan
                            </button>
                        @endif
                        @if($d->laporan_status && ($d->laporan_status instanceof \App\Enums\WfhStatus ? $d->laporan_status->value : $d->laporan_status) === 'rejected')
                            <a href="/wfh/{{ $d->id }}/laporan/edit" class="file-pill bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100">
                                <i data-lucide="pencil"></i> Edit Laporan
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <x-admin.card class="p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#f0f9ff] border border-[#e0f2fe] flex items-center justify-center mx-auto text-[#0284c7]">
                        <i data-lucide="home" class="text-[#0284c7]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Tidak Ada Data WFH</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[28ch] mx-auto">Data WFH yang sudah disetujui akan muncul di sini. Persetujuan dengan status pending hanya di dashboard.</p>
                    <a href="/wfh/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajukan WFH
                    </a>
                </x-admin.card>
            @endforelse
        </div>
    </div>

    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/wfh/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data">
            <i data-lucide="plus"></i>
        </a>
    </div>
@endsection
