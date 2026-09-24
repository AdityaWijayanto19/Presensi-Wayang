@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/cuti" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Upload Cuti Tahunan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
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

            <form method="POST" action="/cuti/store" id="form_cuti" enctype="multipart/form-data" autocomplete="off">
                @csrf

                {{-- Profil Pengaju + Kuota --}}
                <x-admin.card class="p-4 mb-3">
                    <div class="flex items-center gap-3">
                        @php
                            $pathFoto = \Illuminate\Support\Facades\Storage::url('uploads/karyawan/' . $karyawan->foto);
                        @endphp
                        @if ($karyawan->foto && $karyawan->foto !== 'nophoto.png')
                            <img src="{{ url($pathFoto) }}?v={{ time() }}"
                                class="w-10 h-10 rounded-xl object-cover border border-[#f0ece8]"
                                alt="{{ $karyawan->nama_lengkap }}">
                        @else
                            <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}"
                                class="w-10 h-10 rounded-xl object-cover border border-[#f0ece8]"
                                alt="{{ $karyawan->nama_lengkap }}">
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ $karyawan->nama_lengkap }}</div>
                            <div class="text-[12px] text-[#78716c]">{{ $karyawan->posisi }} • {{ $karyawan->unit }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[11px] text-[#a8a29e] uppercase font-semibold">Sisa</div>
                            <div class="text-[20px] font-bold text-emerald-600 leading-none">{{ $sisaCuti }}</div>
                            <div class="text-[10px] text-[#78716c]">/ {{ $jatahCuti }} hari</div>
                        </div>
                    </div>
                </x-admin.card>

                {{-- Durasi Cuti --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        Durasi Cuti <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">
                            <i data-lucide="calendar-range" style="width:20px;height:20px;"></i>
                        </div>
                        <select name="durasi_hari" id="durasi_hari" required
                            class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Durasi --</option>
                            @for ($i = 1; $i <= $maxDurasi; $i++)
                                <option value="{{ $i }}" {{ (string) old('durasi_hari') === (string) $i ? 'selected' : '' }}>
                                    {{ $i }} Hari</option>
                            @endfor
                        </select>
                    </div>
                    <small class="text-[11px] text-[#a8a29e] block mt-1">
                        Maksimal 3 hari per pengajuan. Sisa kuota Anda: {{ $sisaCuti }} hari.
                    </small>
                </div>

                {{-- Tanggal Cuti (dinamis) --}}
                <div class="mb-4" id="tanggalCutiWrapper">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        Tanggal Cuti <span class="text-red-500">*</span>
                    </label>
                    <div id="tanggalCutiFields" class="space-y-2"></div>
                    <small class="text-[11px] text-[#a8a29e] block mt-1">
                        Pilih tanggal cuti satu per satu (boleh tidak berurutan, boleh tanggal lampau).
                    </small>
                </div>

                {{-- Keterangan --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" maxlength="500" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Keterangan cuti (opsional)...">{{ old('keterangan') }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]"><span id="charCount">0</span>/500 karakter</small>
                </div>

                {{-- File Cuti (sudah ditandatangani) --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">File Cuti (Sudah Ditandatangani) <span class="text-red-500">*</span></label>
                    <input type="file" name="bukti_file" id="bukti_file" required
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-coklat file:text-white hover:file:bg-coklat-dark">
                    <small class="text-[11px] text-[#a8a29e]">Format: JPG, JPEG, PNG, PDF (Maks 4MB)</small>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                    <i data-lucide="info"
                        class="text-amber-600 shrink-0 mt-0.5" style="width:18px;height:18px;"></i>
                    <p class="text-[11px] leading-relaxed text-amber-800">Upload cuti akan langsung mengurangi kuota cuti
                        Anda. Pastikan file sudah ditandatangani sebelum diupload.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="upload" style="margin-right:6px;"></i> Upload Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var MAX_DURASI = {{ $maxDurasi }};
            var USED_DATES = @json($usedDates ?? []);
            var durasiSelect = document.getElementById('durasi_hari');
            var fieldsContainer = document.getElementById('tanggalCutiFields');
            var oldTanggal = @json(old('tanggal_cuti', []));
            var pickers = [];

            function destroyPickers() {
                pickers.forEach(function(p) { try { p.destroy(); } catch (e) {} });
                pickers = [];
            }

            function renderTanggalFields(count) {
                destroyPickers();
                fieldsContainer.innerHTML = '';
                for (var i = 0; i < count; i++) {
                    var wrap = document.createElement('div');
                    wrap.className = 'relative flex items-center';
                    wrap.innerHTML =
                        '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">' +
                        '<i data-lucide="calendar" style="width:18px;height:18px;"></i></div>' +
                        '<input type="text" name="tanggal_cuti[]" class="cuti-tanggal w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" ' +
                        'placeholder="Pilih Tanggal ' + (i + 1) + '" autocomplete="off" required value="' + (oldTanggal[i] || '') + '">';
                    fieldsContainer.appendChild(wrap);

                    var input = wrap.querySelector('input');
                    var fp = flatpickr(input, {
                        locale: "id",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j F Y",
                        allowInput: true,
                        disableMobile: true,
                        disable: USED_DATES.slice()
                    });
                    pickers.push(fp);
                }
                if (window.lucide) lucide.createIcons();
            }

            function currentDurasi() {
                var v = parseInt(durasiSelect.value, 10);
                return (v >= 1 && v <= MAX_DURASI) ? v : 0;
            }

            durasiSelect.addEventListener('change', function() {
                renderTanggalFields(currentDurasi());
            });

            renderTanggalFields(currentDurasi());

            // Char count
            var el = document.getElementById('keterangan');
            document.getElementById('charCount').textContent = el.value.length;
            el.addEventListener("input", function() {
                document.getElementById('charCount').textContent = this.value.length;
            });

            // Submit validation
            document.getElementById('form_cuti').addEventListener('submit', function(e) {
                e.preventDefault();
                var durasi = currentDurasi();
                var tanggalInputs = document.querySelectorAll('.cuti-tanggal');
                var fileInput = document.getElementById('bukti_file');
                var keterangan = el.value.trim();
                var tanggalValues = [];
                var validTanggal = true;

                if (!durasi) {
                    Swal.fire({ icon: "warning", text: "Durasi cuti harus dipilih", confirmButtonColor: "#7a5234" });
                    return;
                }

                tanggalInputs.forEach(function(input) {
                    var alt = input._flatpickr ? input._flatpickr.input.value : input.value;
                    if (!alt) validTanggal = false;
                    tanggalValues.push(alt);
                });

                if (!validTanggal || tanggalValues.filter(Boolean).length !== durasi) {
                    Swal.fire({ icon: "warning", text: "Semua tanggal cuti harus diisi sesuai durasi", confirmButtonColor: "#7a5234" });
                    return;
                }

                if (new Set(tanggalValues).size !== tanggalValues.length) {
                    Swal.fire({ icon: "warning", text: "Tanggal cuti tidak boleh sama", confirmButtonColor: "#7a5234" });
                    return;
                }

                var sudahDipakai = tanggalValues.filter(function (t) { return USED_DATES.indexOf(t) !== -1; });
                if (sudahDipakai.length) {
                    Swal.fire({ icon: "warning", text: "Tanggal " + sudahDipakai.join(", ") + " sudah pernah dipilih. Pilih tanggal lain.", confirmButtonColor: "#7a5234" });
                    return;
                }

                if (keterangan.length > 500) {
                    Swal.fire({ icon: "warning", text: "Keterangan maksimal 500 karakter", confirmButtonColor: "#7a5234" });
                    return;
                }

                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({ icon: "warning", text: "File cuti wajib diupload", confirmButtonColor: "#7a5234" });
                    return;
                }
                var allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                var file = fileInput.files[0];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({ icon: "warning", text: "Format file tidak valid. Gunakan JPG, PNG, atau PDF.", confirmButtonColor: "#7a5234" });
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    Swal.fire({ icon: "warning", text: "Ukuran file maksimal 4MB", confirmButtonColor: "#7a5234" });
                    return;
                }

                Swal.fire({
                    title: "Upload Cuti Tahunan?",
                    text: "Durasi: " + durasi + " hari. Kuota cuti akan berkurang " + durasi + " hari.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#7a5234",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Upload",
                    cancelButtonText: "Batal"
                }).then(function(r) {
                    if (r.isConfirmed) document.getElementById('form_cuti').submit();
                });
            });
        });
    </script>
@endpush
