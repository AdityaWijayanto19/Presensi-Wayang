@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/izin" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Pengajuan Izin</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if (Session::get('success'))
                <div class="bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">
                    {{ Session::get('success') }}</div>
            @endif
            @if (Session::get('error'))
                <div class="bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">
                    {{ Session::get('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">
                    <ul class="mb-0 list-disc pl-4">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/izin/store" id="form_izin" enctype="multipart/form-data" autocomplete="off">
                @csrf

                {{-- Auto Info --}}
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
                            <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Pengaju</div>
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ $karyawan->nama_lengkap }}</div>
                            <div class="text-[12px] text-[#78716c]">{{ $karyawan->posisi }} • {{ $karyawan->unit }}
                                ({{ $karyawan->unitperusahaan->perusahaan ?? '' }})</div>
                        </div>
                    </div>
                </x-admin.card>

                {{-- Kategori Izin --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        Kategori Izin <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">
                            <i data-lucide="tag" style="width:20px;height:20px;"></i>
                        </div>
                        <select name="jenis_izin" id="jenis_izin" required
                            class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="tidak_masuk" {{ old('jenis_izin') == 'tidak_masuk' ? 'selected' : '' }}>Izin Tidak Masuk</option>
                            <option value="terlambat" {{ old('jenis_izin') == 'terlambat' ? 'selected' : '' }}>Izin Terlambat</option>
                            <option value="pulang_cepat" {{ old('jenis_izin') == 'pulang_cepat' ? 'selected' : '' }}>Izin Pulang Cepat</option>
                            <option value="sakit" {{ old('jenis_izin') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        </select>
                    </div>
                    <small id="jenisHelp" class="text-[11px] text-[#a8a29e] block mt-1">Pilih kategori izin yang sesuai.</small>
                </div>

                {{-- Tanggal Izin --}}
                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        Tanggal Izin <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">
                            <i data-lucide="calendar-clock" style="width:20px;height:20px;"></i>
                        </div>
                        <input type="text"
                            class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            name="tgl_izin" id="tgl_izin" placeholder="Pilih Tanggal Izin" autocomplete="off" required
                            value="{{ old('tgl_izin') }}">
                    </div>
                    <div class="mt-1">
                        @if ($disableToday)
                            <small class="text-[11px] text-red-500 block">
                                Batas pengajuan izin hari ini sudah lewat (maks 1 jam setelah jam masuk).
                            </small>
                        @else
                            <small class="text-[11px] text-[#a8a29e] block">
                                Untuk hari ini, maksimal 1 jam setelah jam masuk.
                            </small>
                        @endif
                    </div>
                </div>

                {{-- Keterangan --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="keterangan" id="keterangan" rows="3" maxlength="500" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Jelaskan alasan izin Anda..." required>{{ old('keterangan') }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]"><span id="charCount">0</span>/500 karakter</small>
                </div>

                {{-- Bukti File --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Bukti File <span class="text-red-500">*</span></label>
                    <input type="file" name="bukti_file" id="bukti_file" required
                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-coklat file:text-white hover:file:bg-coklat-dark">
                    <small class="text-[11px] text-[#a8a29e]">Format: JPG, JPEG, PNG, PDF, DOC, DOCX (Maks 4MB)</small>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                    <i data-lucide="info"
                        class="text-amber-600 shrink-0 mt-0.5" style="width:18px;height:18px;"></i>
                    <p class="text-[11px] leading-relaxed text-amber-800">Setelah submit, pengajuan izin akan menunggu
                        persetujuan atasan terlebih dahulu, kemudian diteruskan ke HR.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="send" style="margin-right:6px;"></i> Ajukan Izin
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var SERVER_TODAY = '{{ now("Asia/Jakarta")->format("Y-m-d") }}';
            var SERVER_DISABLE_TODAY = {{ $disableToday ? 'true' : 'false' }};

            var minDateSetting = SERVER_DISABLE_TODAY
                ? '{{ now("Asia/Jakarta")->addDay()->format("Y-m-d") }}'
                : SERVER_TODAY;

            flatpickr("#tgl_izin", {
                locale: "id",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                allowInput: true,
                disableMobile: true,
                minDate: minDateSetting,
                disable: [
                    function(date) {
                        if (SERVER_DISABLE_TODAY) {
                            var dateStr = date.getFullYear() + '-'
                                + String(date.getMonth() + 1).padStart(2, '0') + '-'
                                + String(date.getDate()).padStart(2, '0');
                            if (dateStr === SERVER_TODAY) return true;
                        }
                        return false;
                    }
                ],
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    if (SERVER_DISABLE_TODAY) {
                        var dateStr = dayElem.dateObj.getFullYear() + '-'
                            + String(dayElem.dateObj.getMonth() + 1).padStart(2, '0') + '-'
                            + String(dayElem.dateObj.getDate()).padStart(2, '0');
                        if (dateStr === SERVER_TODAY) {
                            dayElem.classList.add('fp-today-disabled', 'flatpickr-disabled');
                            dayElem.style.setProperty('background', '#fee2e2', 'important');
                            dayElem.style.setProperty('border-color', '#fca5a5', 'important');
                            dayElem.style.setProperty('color', '#991b1b', 'important');
                            dayElem.style.setProperty('text-decoration', 'line-through', 'important');
                            dayElem.style.setProperty('opacity', '0.7', 'important');
                            dayElem.style.setProperty('cursor', 'not-allowed', 'important');
                        }
                    }
                }
            });

            // Char count
            var el = document.getElementById('keterangan');
            document.getElementById('charCount').textContent = el.value.length;
            el.addEventListener("input", function() {
                document.getElementById('charCount').textContent = this.value.length;
            });

            // Kategori help text
            var jenisSelect = document.getElementById('jenis_izin');
            var jenisHelp = document.getElementById('jenisHelp');
            var helpTexts = {
                'tidak_masuk': 'Anda mengajukan izin tidak masuk kerja pada tanggal yang dipilih.',
                'terlambat': 'Anda mengajukan izin terlambat masuk kerja.',
                'pulang_cepat': 'Anda mengajukan izin pulang lebih awal dari jam normal.',
                'sakit': 'Anda mengajukan izin sakit. Pastikan bukti file berupa surat dokter.'
            };
            jenisSelect.addEventListener('change', function() {
                jenisHelp.textContent = helpTexts[this.value] || 'Pilih kategori izin yang sesuai.';
            });

            // Submit validation
            document.getElementById('form_izin').addEventListener('submit', function(e) {
                e.preventDefault();
                var jenis = jenisSelect.value;
                var tgl = document.getElementById('tgl_izin').value;
                var keterangan = el.value.trim();
                var fileInput = document.getElementById('bukti_file');

                if (!jenis) {
                    Swal.fire({ icon: "warning", text: "Kategori izin harus dipilih", confirmButtonColor: "#7a5234" });
                    return;
                }
                if (!tgl) {
                    Swal.fire({ icon: "warning", text: "Tanggal izin harus diisi", confirmButtonColor: "#7a5234" });
                    return;
                }
                if (!keterangan || keterangan.length < 5) {
                    Swal.fire({ icon: "warning", text: "Keterangan minimal 5 karakter", confirmButtonColor: "#7a5234" });
                    return;
                }
                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({ icon: "warning", text: "Bukti file wajib diupload", confirmButtonColor: "#7a5234" });
                    return;
                }
                var allowedTypes = ['image/jpeg','image/png','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                var file = fileInput.files[0];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({ icon: "warning", text: "Format file tidak valid. Gunakan JPG, PNG, PDF, DOC, atau DOCX.", confirmButtonColor: "#7a5234" });
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    Swal.fire({ icon: "warning", text: "Ukuran file maksimal 4MB", confirmButtonColor: "#7a5234" });
                    return;
                }

                var jenisLabels = {
                    'tidak_masuk': 'Izin Tidak Masuk',
                    'terlambat': 'Izin Terlambat',
                    'pulang_cepat': 'Izin Pulang Cepat',
                    'sakit': 'Sakit'
                };

                Swal.fire({
                    title: "Ajukan Izin?",
                    text: "Kategori: " + (jenisLabels[jenis] || jenis) + ". Data akan diproses dan dikirim untuk persetujuan.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#7a5234",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Ajukan",
                    cancelButtonText: "Batal"
                }).then(function(r) {
                    if (r.isConfirmed) document.getElementById('form_izin').submit();
                });
            });
        });
    </script>
@endpush
