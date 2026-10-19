@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/cuti" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Upload File Cuti — Langkah 2</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $tanggalList = is_array($cuti->tanggal_cuti) ? $cuti->tanggal_cuti : (json_decode($cuti->tanggal_cuti, true) ?: []);
        $tanggalDisplay = collect($tanggalList)->map(fn ($t) => date('d M Y', strtotime($t)))->implode(', ');
        $isRejected = $cuti->status instanceof \App\Enums\CutiStatus && $cuti->status === \App\Enums\CutiStatus::Rejected;
        $formUrl = !empty($cuti->pdf_form_path) ? '/cuti/' . $cuti->id . '/pdf' : null;
    @endphp

    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if ($errors->any())
                <div class="bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">
                    <ul class="mb-0 list-disc pl-4">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Progress: langkah 1 selesai, langkah 2 aktif --}}
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500 text-white text-[11px] font-bold">✓</span>
                <span class="text-[12px] font-semibold text-[#44403c]">Isi Data Pengajuan</span>
                <span class="h-px flex-1 bg-[#e7e5e4]"></span>
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-coklat text-white text-[11px] font-bold">2</span>
                <span class="text-[12px] font-semibold text-[#44403c]">Upload File Ditandatangani</span>
            </div>

            @if ($isRejected && !empty($cuti->rejected_reason))
                <div class="bg-[#fef2f2] border border-[#fecaca] rounded-xl p-3 mb-3">
                    <div class="text-[12px] font-bold text-[#991b1b] mb-1">Pengajuan Ditolak</div>
                    <div class="text-[11px] text-[#b91c1c] leading-relaxed">{{ $cuti->rejected_reason }}</div>
                    <div class="text-[11px] text-[#991b1b] mt-1.5">Silakan upload ulang file hasil print &amp; tanda tangan terbaru (data tidak perlu diulang).</div>
                </div>
            @endif

            {{-- Ringkasan pengajuan --}}
            <x-admin.card class="p-4 mb-3">
                <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Ringkasan Pengajuan</div>
                <div class="mt-2 space-y-1.5 text-[13px]">
                    <div class="flex justify-between gap-2">
                        <span class="text-[#78716c]">Durasi</span>
                        <span class="font-semibold text-[#1c1917]">{{ $cuti->durasi_hari }} Hari</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-[#78716c]">Tanggal</span>
                        <span class="font-semibold text-[#1c1917] text-right">{{ $tanggalDisplay }}</span>
                    </div>
                    @if (!empty($cuti->keterangan))
                        <div class="flex justify-between gap-2">
                            <span class="text-[#78716c]">Keterangan</span>
                            <span class="text-[#44403c] text-right max-w-[60%]">{{ $cuti->keterangan }}</span>
                        </div>
                    @endif
                </div>
            </x-admin.card>

            {{-- Download formulir PDF --}}
            @if ($formUrl)
                <x-admin.card class="p-4 mb-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="text-sky-600" style="width:20px;height:20px;"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-bold text-[#1c1917]">Formulir Surat Cuti</div>
                            <div class="text-[11px] text-[#78716c] leading-relaxed mt-0.5">Download &amp; cetak formulir ini, lalu tanda tangani pada kolom yang tersedia.</div>
                            <div class="mt-2 flex gap-2 flex-wrap">
                                <a href="{{ $formUrl }}" target="_blank" class="file-pill bg-sky-50 border-sky-200 text-sky-700 hover:bg-sky-100">
                                    <i data-lucide="download"></i> Download PDF
                                </a>
                                <a href="{{ $formUrl }}" target="_blank" rel="noopener" class="file-pill">
                                    <i data-lucide="eye"></i> Preview
                                </a>
                            </div>
                        </div>
                    </div>
                </x-admin.card>
            @endif

            {{-- Upload file hasil tanda tangan --}}
            <form method="POST" action="/cuti/{{ $cuti->id }}/upload" id="form_upload_cuti" enctype="multipart/form-data" autocomplete="off">
                @csrf

                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        File Cuti yang Sudah Ditandatangani <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">
                            <i data-lucide="paperclip" style="width:20px;height:20px;"></i>
                        </div>
                        <input type="file" name="bukti_file" id="bukti_file" accept=".jpg,.jpeg,.png,.pdf" required
                            class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-coklat file:text-white">
                    </div>
                    <small class="text-[11px] text-[#a8a29e] block mt-1">
                        Format: JPG, JPEG, PNG, atau PDF. Maksimal 4MB.
                    </small>
                </div>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mt-3 flex gap-2.5">
                    <i data-lucide="info"
                        class="text-emerald-600 shrink-0 mt-0.5" style="width:18px;height:18px;"></i>
                    <p class="text-[11px] leading-relaxed text-emerald-900">Setelah file diupload, pengajuan akan
                        <b>menunggu persetujuan superadmin</b>. Kuota cuti baru berkurang setelah disetujui.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="upload" style="margin-right:6px;"></i>
                        Upload &amp; Ajukan
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="/cuti/{{ $cuti->id }}/edit" class="text-[12px] font-semibold text-[#78716c] underline">Ubah data pengajuan (langkah 1)</a>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fileInput = document.getElementById('bukti_file');
            var form = document.getElementById('form_upload_cuti');
            var MAX_SIZE = 4 * 1024 * 1024;
            var ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'pdf'];

            fileInput.addEventListener('change', function() {
                var f = this.files && this.files[0];
                if (!f) return;
                var ext = (f.name.split('.').pop() || '').toLowerCase();
                if (ALLOWED_EXT.indexOf(ext) === -1) {
                    Swal.fire({ icon: 'warning', text: 'Format file tidak valid. Gunakan JPG, JPEG, PNG, atau PDF.', confirmButtonColor: '#7a5234' });
                    this.value = '';
                    return;
                }
                if (f.size > MAX_SIZE) {
                    Swal.fire({ icon: 'warning', text: 'Ukuran file maksimal 4MB', confirmButtonColor: '#7a5234' });
                    this.value = '';
                }
            });

            form.addEventListener('submit', function(e) {
                if (!fileInput.files || !fileInput.files.length) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', text: 'File cuti wajib diupload', confirmButtonColor: '#7a5234' });
                }
            });
        });
    </script>
@endpush
