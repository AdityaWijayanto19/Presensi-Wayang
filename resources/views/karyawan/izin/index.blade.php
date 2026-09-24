@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
         <div class="left">
            <a href="/pengajuan" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Data Izin</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $weekdayMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];

        $statusLabels = [
            'pending_atasan' => 'Menunggu Atasan',
            'pending_admin' => 'Menunggu HR',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        $statusBadgeClasses = [
            'pending_atasan' => 'bg-amber-100 text-amber-700 border-amber-200',
            'pending_admin' => 'bg-sky-100 text-sky-700 border-sky-200',
            'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 border-rose-200',
        ];
        $jenisLabels = [
            'tidak_masuk' => 'Tidak Masuk',
            'terlambat' => 'Terlambat',
            'setengah_hari' => 'Setengah Hari',
            'pulang_cepat' => 'Pulang Cepat',
            'sakit' => 'Sakit',
        ];
        $jenisBadgeClasses = [
            'tidak_masuk' => 'bg-amber-100 text-amber-700 border-amber-200',
            'terlambat' => 'bg-orange-100 text-orange-700 border-orange-200',
            'setengah_hari' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'pulang_cepat' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'sakit' => 'bg-rose-100 text-rose-700 border-rose-200',
        ];
    @endphp

    @if ($dataizin->count() > 0)
        @php
            $approvedCount = $dataizin->where('status', \App\Enums\IzinStatus::Approved)->count();
            $rejectedCount = $dataizin->where('status', \App\Enums\IzinStatus::Rejected)->count();
        @endphp
        <div class="flex mt-3">
            <div class="w-full px-3">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold tracking-wide text-[#a8a29e] uppercase">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="check-check" class="text-emerald-600" style="width:13px;height:13px;"></i> {{ $dataizin->count() }} Data</span>
                        <span class="mx-1.5 text-[#e7e5e4]">•</span>
                        @if ($approvedCount > 0)
                            <span class="text-emerald-700">{{ $approvedCount }} Disetujui</span>
                        @endif
                        @if ($rejectedCount > 0)
                            @if ($approvedCount > 0)
                                <span class="mx-1.5 text-[#e7e5e4]">•</span>
                            @endif
                            <span class="text-rose-700">{{ $rejectedCount }} Ditolak</span>
                        @endif
                    </p>
                    <span class="text-[11px] font-medium text-[#78716c] bg-white border border-[#f0ece8] rounded-full px-2.5 py-1">{{ date('M Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="flex mt-3">
        <div class="w-full px-3">
            @forelse ($dataizin as $d)
                @php
                    $ts = strtotime($d->tgl_izin);
                    $weekday = $weekdayMap[date('l', $ts)] ?? date('l', $ts);
                    $displayDate = date('d M Y', $ts);
                    $status = $d->status instanceof \App\Enums\IzinStatus ? $d->status->value : ($d->status ?? 'approved');
                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                    $badgeClass = $statusBadgeClasses[$status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    $jenis = $d->jenis_izin instanceof \App\Enums\JenisIzin ? $d->jenis_izin->value : ($d->jenis_izin ?? '');
                    $jenisLabel = $jenisLabels[$jenis] ?? $jenis;
                    $jenisBadge = $jenisBadgeClasses[$jenis] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    $pdfUrl = !empty($d->pdf_form_path) ? \Illuminate\Support\Facades\Storage::url($d->pdf_form_path) : "#";
                    $pdfName = !empty($d->pdf_form_path) ? basename($d->pdf_form_path) : '';
                    $buktiUrl = !empty($d->bukti_file) ? \Illuminate\Support\Facades\Storage::disk('public')->url('uploads/izin/' . $d->bukti_file) : null;
                @endphp
                <div class="presensi-card mb-2.5">
                    <div class="flex items-start gap-3">
                        <div class="presensi-icon-box icon-izin">
                            <i data-lucide="file-text"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[14.5px] font-bold text-[#1c1917] tracking-tight leading-none">{{ $displayDate }}</span>
                                <span class="presensi-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <i data-lucide="calendar" class="text-[#a8a29e]" style="width:12px;height:12px;"></i>
                                <span class="text-[12px] font-medium text-[#78716c]">{{ $weekday }}</span>
                                <span class="presensi-badge {{ $jenisBadge }} text-[10px] py-0 px-1.5">{{ $jenisLabel }}</span>
                            </div>
                            @if (!empty($d->keterangan))
                                <div class="text-[12px] text-[#57534e] mt-1.5 leading-snug">{{ Str::limit($d->keterangan, 80) }}</div>
                            @endif
                            @if($status === 'rejected' && !empty($d->rejected_reason))
                                <div class="mt-1 text-[11px] text-rose-500">Alasan: {{ $d->rejected_reason }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="presensi-divider"></div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if($pdfUrl !== "#")
                            <button type="button" class="file-pill js-preview" data-url="{{ $pdfUrl }}" data-filename="{{ $pdfName }}" data-label="Form Izin — {{ $displayDate }}">
                                <i data-lucide="file"></i> Form
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-[#f0f9ff] border border-[#bae6fd] text-[9px] font-bold text-[#0369a1] uppercase">{{ strtolower(pathinfo($pdfName, PATHINFO_EXTENSION)) }}</span>
                            </button>
                        @endif
                        @if($buktiUrl)
                            <button type="button" class="file-pill js-preview" data-url="{{ $buktiUrl }}" data-filename="{{ basename($buktiUrl) }}" data-label="Bukti Izin — {{ $displayDate }}">
                                <i data-lucide="paperclip"></i> Bukti
                            </button>
                        @endif
                        @if($status === 'rejected')
                            <a href="/izin/{{ $d->id }}/edit" class="file-pill bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100">
                                <i data-lucide="pencil"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <x-admin.card class="p-8 mt-6 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-[#fff1f2] border border-[#fecdd3] flex items-center justify-center mx-auto text-[#e11d48]">
                        <i data-lucide="file-text" class="text-[#e11d48]" style="width:40px;height:40px;"></i>
                    </div>
                    <h4 class="mt-4 text-[16px] font-bold text-[#1c1917]">Belum Ada Riwayat Izin</h4>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-[#78716c] max-w-[28ch] mx-auto">Riwayat izin yang sudah disetujui atau ditolak akan muncul di sini.</p>
                    <a href="/izin/create" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-full bg-coklat text-white text-[13px] font-semibold shadow-sm hover:bg-coklat-dark transition">
                        <i data-lucide="plus" style="width:16px;height:16px;"></i> Ajukan Izin
                    </a>
                </x-admin.card>
            @endforelse
        </div>
    </div>

    <div class="fab-button bottom-right" style="bottom: 78px; right: 16px;">
        <a href="/izin/create" class="fab bg-coklat text-white shadow-lg" aria-label="Tambah Data">
            <i data-lucide="plus"></i>
        </a>
    </div>
@endsection
