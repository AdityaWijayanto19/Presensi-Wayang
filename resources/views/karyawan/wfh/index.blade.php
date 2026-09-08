@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">Data Work From Home</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $messagesuccess = Session::get('success');
        $messageerror = Session::get('error');
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    @endphp

    {{-- Alert --}}
    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if ($messagesuccess)
                <div class="flex items-center gap-2.5 bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[13px] font-medium rounded-xl py-2.5 px-3.5" id="alert-success">
                    <i data-lucide="circle-check" class="text-[#10b981] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messagesuccess }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="shrink-0 w-6 h-6 rounded-full bg-white border border-[#a7f3d0] flex items-center justify-center text-[#065f46]"><i data-lucide="x" style="width:14px;height:14px;"></i></button>
                </div>
            @endif
            @if ($messageerror)
                <div class="flex items-center gap-2.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] font-medium rounded-xl py-2.5 px-3.5">
                    <i data-lucide="circle-alert" class="text-[#ef4444] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messageerror }}</span>
                </div>
            @endif
        </div>
    </div>

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
                        'pending_admin' => 'Menunggu Admin',
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
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#f0ece8] shadow-sm p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#f0f9ff] border border-[#e0f2fe] flex items-center justify-center mx-auto text-[#0284c7]">
                        <i data-lucide="home" class="text-[#0284c7]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Tidak Ada Data WFH</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[28ch] mx-auto">Data WFH yang sudah disetujui akan muncul di sini. Persetujuan dengan status pending hanya di dashboard.</p>
                    <a href="/wfh/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajukan WFH
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/wfh/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data">
            <i data-lucide="plus"></i>
        </a>
    </div>

    <script>
        setTimeout(function () {
            let alert = document.getElementById('alert-success');
            if (alert) { alert.style.opacity = '0'; alert.style.transition = 'opacity 0.3s'; setTimeout(() => alert.style.display = 'none', 300); }
        }, 3000);
    </script>
@endsection
