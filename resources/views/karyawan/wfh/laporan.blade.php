@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/wfh" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">{{ $isEdit ?? false ? 'Edit Laporan WFH' : 'Input Laporan WFH' }}</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $karyawan = Auth::guard('karyawan')->user();
    @endphp

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

            <form method="POST" action="{{ $isEdit ?? false ? '/wfh/' . $wfh->id . '/laporan/update' : '/wfh/' . $wfh->id . '/laporan' }}" enctype="multipart/form-data" id="form_laporan">
                @csrf

                @if($isEdit ?? false)
                    @if($wfh->laporan_rejected_reason)
                        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-3 flex gap-2.5">
                            <i data-lucide="alert-circle" class="text-rose-600 shrink-0 mt-0.5" style="width:18px;height:18px;"></i>
                            <div>
                                <p class="text-[12px] font-semibold text-rose-700">Laporan Ditolak</p>
                                <p class="text-[11px] text-rose-600">{{ $wfh->laporan_rejected_reason }}</p>
                            </div>
                        </div>
                    @endif
                @endif

                <x-admin.card class="p-4 mb-3">
                    {{-- items-center membuat foto persis di tengah secara vertikal --}}
                    <div class="flex items-center gap-3">

                        {{-- Foto Profil --}}
                        @php
                            $pathFoto = \Illuminate\Support\Facades\Storage::url('uploads/karyawan/' . $karyawan->foto);
                        @endphp
                        @if ($karyawan->foto && $karyawan->foto !== 'nophoto.png')
                            <img src="{{ url($pathFoto) }}?v={{ time() }}"
                                class="w-12 h-12 rounded-xl object-cover border border-[#f0ece8] shrink-0"
                                alt="{{ $karyawan->nama_lengkap }}">
                        @else
                            <img src="{{ asset('assets/img/sample/avatar/avatar1.jpg') }}"
                                class="w-12 h-12 rounded-xl object-cover border border-[#f0ece8] shrink-0"
                                alt="{{ $karyawan->nama_lengkap }}">
                        @endif

                        {{-- Detail Informasi (space-y-0.5 menjaga jarak antar-baris tetap dekat/rapat) --}}
                        <div class="flex-1 min-w-0 space-y-0.5">
                            <div class="text-[10px] font-semibold tracking-wide text-[#a8a29e] uppercase leading-none">
                                Pengaju
                            </div>

                            <div class="text-[14px] font-bold text-[#1c1917] truncate leading-tight">
                                {{ $karyawan->nama_lengkap }}
                            </div>

                            <div class="text-[12px] text-[#78716c] truncate leading-tight">
                                {{ $karyawan->posisi }} • {{ $karyawan->unit }}
                                ({{ $karyawan->unitperusahaan->perusahaan ?? '' }})
                            </div>

                            {{-- Metadata: Tanggal & Lokasi --}}
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[12px] text-[#1c1917]">
                                {{-- Tanggal --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-600 shrink-0"></i>
                                    <span class="font-bold">{{ date('d M Y', strtotime($wfh->tgl_wfh)) }}</span>
                                </div>

                                <span class="text-[#a8a29e] hidden sm:inline">•</span>

                                {{-- Lokasi Live --}}
                                <div class="flex items-center gap-1 min-w-0 flex-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-green-600 shrink-0"></i>
                                    <span class="font-bold truncate"
                                        title="{{ $liveLocation }}">{{ $liveLocation }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </x-admin.card>

                {{-- Detail Hasil Pekerjaan (Deskripsi) --}}
                <div class="form-group mb-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Deskripsi Hasil Pekerjaan <span
                            class="text-red-500">*</span></label>
                    <textarea name="laporan_deskripsi" id="deskripsi_laporan" rows="5" maxlength="3000"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="1. Menuliskan list pekerjaan&#10;2. List pekerjaan dibuat numerik/berurutan&#10;3. Dokumentasikan hasil kerja"
                        required>{{ old('laporan_deskripsi', $wfh->laporan_deskripsi) }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]"><span id="charCountDesk">0</span>/3000 karakter &bull;
                        Maksimal 10 poin</small>
                </div>

                {{-- Upload Gambar Hasil Pekerjaan (Min 2, Max 5) --}}
                <div class="form-group mb-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Foto Hasil Pekerjaan <span
                            class="text-red-500">*</span></label>
                    <input type="file" name="laporan_images[]" id="laporan_images"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        accept="image/*" multiple>
                    <small class="text-[11px] text-[#a8a29e]">Minimal 2 foto, maksimal 5 foto. Format: JPG, JPEG, PNG. Maks
                        4MB per foto.</small>
                    <div id="image-preview" class="flex flex-wrap gap-2 mt-2"></div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="circle-check" style="margin-right:6px;"></i> {{ $isEdit ?? false ? 'Update Laporan' : 'Kirim Laporan' }}
                    </button>
                    <p class="text-[11px] text-[#a8a29e] text-center mt-2">{{ $isEdit ?? false ? 'Laporan yang diperbarui akan dikirim ulang untuk persetujuan.' : 'Laporan akan melalui persetujuan atasan dan administrator.' }}</p>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('myscript')



    <div id="imgPreviewModal" class="img-preview-modal">
        <button type="button" class="btn-close-preview" id="imgPreviewClose">&times;</button>
        <img id="imgPreviewEl" src="" alt="Preview">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('laporan_images');
            const previewContainer = document.getElementById('image-preview');
            const imgPreviewModal = document.getElementById('imgPreviewModal');
            const imgPreviewEl = document.getElementById('imgPreviewEl');
            const imgPreviewClose = document.getElementById('imgPreviewClose');

            let selectedFiles = [];

            function syncInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(f => dt.items.add(f));
                fileInput.files = dt.files;
            }

            function renderPreviews() {
                previewContainer.innerHTML = '';
                selectedFiles.forEach(function(file, idx) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-thumb';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = file.name;
                        div.appendChild(img);

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn-remove';
                        btn.innerHTML = '&times;';
                        btn.setAttribute('aria-label', 'Hapus foto');
                        btn.addEventListener('click', function(ev) {
                            ev.stopPropagation();
                            selectedFiles.splice(idx, 1);
                            syncInput();
                            renderPreviews();
                            fileInput.setCustomValidity(selectedFiles.length === 0 ?
                                'required' : '');
                        });
                        div.appendChild(btn);

                        div.addEventListener('click', function() {
                            imgPreviewEl.src = e.target.result;
                            imgPreviewModal.classList.add('open');
                            document.body.style.overflow = 'hidden';
                        });

                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }

            function closeImgPreview() {
                imgPreviewModal.classList.remove('open');
                document.body.style.overflow = '';
            }

            imgPreviewClose.addEventListener('click', closeImgPreview);
            imgPreviewModal.addEventListener('click', function(e) {
                if (e.target === imgPreviewModal) closeImgPreview();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && imgPreviewModal.classList.contains('open')) closeImgPreview();
            });

            fileInput.addEventListener('change', function() {
                const incoming = Array.from(this.files);
                const rejected = [];

                incoming.forEach(function(file) {
                    if (file.size > 4 * 1024 * 1024) {
                        rejected.push(file.name);
                        return;
                    }
                    if (selectedFiles.length >= 5) return;
                    selectedFiles.push(file);
                });

                if (rejected.length) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Ukuran foto maksimal 4MB: ' + rejected.join(', '),
                        confirmButtonColor: '#7a5234'
                    });
                }
                if (selectedFiles.length > 5) {
                    selectedFiles = selectedFiles.slice(0, 5);
                    Swal.fire({
                        icon: 'warning',
                        text: 'Maksimal 5 foto hasil pekerjaan',
                        confirmButtonColor: '#7a5234'
                    });
                }

                syncInput();
                renderPreviews();
                fileInput.setCustomValidity('');
            });

            // ── Deskripsi Auto-Numbering ──────────────────────────
            var desk = document.getElementById('deskripsi_laporan');
            var MAX_PPOINT = 10;

            function maxLineNumberDesk(text) {
                var max = 0;
                text.split("\n").forEach(function(l) {
                    var m = l.match(/^(\d+)\.\s/);
                    if (m) max = Math.max(max, parseInt(m[1]));
                });
                return max;
            }

            function renumberDesk(text) {
                var lines = text.split("\n");
                var out = [],
                    num = 1;
                for (var i = 0; i < lines.length; i++) {
                    var content = lines[i].replace(/^\d+[\.\s]*/, "");
                    if (content === "" && i === lines.length - 1) {
                        out.push("");
                    } else if (content === "") {
                        continue;
                    } else {
                        out.push(num + ". " + content);
                        num++;
                    }
                }
                return out.join("\n");
            }

            desk.addEventListener("focus", function() {
                if (this.value === "") this.value = "1. ";
            });

            desk.addEventListener("keydown", function(e) {
                var val = this.value;
                var pos = this.selectionStart;

                if (e.keyCode === 13) {
                    e.preventDefault();
                    var lines = val.split("\n");
                    var trailing = lines[lines.length - 1] === "";
                    var totalLines = trailing ? lines.length - 1 : lines.length;
                    if (totalLines >= MAX_PPOINT) return;

                    var nextNum = maxLineNumberDesk(val) + 1;
                    var cleanVal = trailing ? val.substring(0, val.length - 1) : val;
                    var cleanPos = Math.min(pos, cleanVal.length);
                    var before = cleanVal.substring(0, cleanPos);
                    var after = cleanVal.substring(cleanPos);

                    this.value = before + "\n" + nextNum + ". " + after;
                    var newPos = before.length + 1 + String(nextNum).length + 2;
                    this.setSelectionRange(newPos, newPos);
                    return;
                }

                if (e.keyCode === 8 && pos > 0) {
                    var beforeCursor = val.substring(0, pos);
                    var lines = val.split("\n");
                    var lineIdx = beforeCursor.split("\n").length - 1;
                    var currentLine = lines[lineIdx];

                    if (/^\d+\.?\s?$/.test(currentLine)) {
                        e.preventDefault();
                        if (lines.length <= 1) {
                            this.value = "";
                            this.setSelectionRange(0, 0);
                            return;
                        }
                        lines.splice(lineIdx, 1);
                        var newVal = renumberDesk(lines.join("\n"));
                        this.value = newVal;
                        var targetIdx = Math.max(0, lineIdx - 1);
                        var newLines = newVal.split("\n");
                        var cursorPos = 0;
                        for (var i = 0; i < targetIdx; i++) cursorPos += newLines[i].length + 1;
                        cursorPos += newLines[targetIdx].length;
                        this.setSelectionRange(cursorPos, cursorPos);
                        return;
                    }

                    var lineStart = beforeCursor.lastIndexOf("\n") + 1;
                    var linePrefix = currentLine.match(/^(\d+)\.\s/);
                    if (linePrefix && pos === lineStart + linePrefix[0].length) {
                        e.preventDefault();
                        var prevLineIdx = lineIdx - 1;
                        if (prevLineIdx < 0) return;
                        var prevLine = lines[prevLineIdx];
                        var content = currentLine.replace(/^\d+\.\s/, "");
                        lines[prevLineIdx] = prevLine + content;
                        lines.splice(lineIdx, 1);
                        var newVal = renumberDesk(lines.join("\n"));
                        this.value = newVal;
                        var newLines = newVal.split("\n");
                        var cursorPos = 0;
                        for (var i = 0; i < prevLineIdx; i++) cursorPos += newLines[i].length + 1;
                        cursorPos += prevLine.length;
                        this.setSelectionRange(cursorPos, cursorPos);
                        return;
                    }
                }
            });

            desk.addEventListener("input", function() {
                document.getElementById('charCountDesk').textContent = this.value.length;
            });

            document.getElementById('charCountDesk').textContent = desk.value.length;

            document.getElementById('form_laporan').addEventListener('submit', function(e) {
                e.preventDefault();
                const deskValue = document.querySelector('textarea[name="laporan_deskripsi"]').value.trim();
                if (!deskValue || deskValue.length < 10) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Deskripsi minimal 10 karakter',
                        confirmButtonColor: '#7a5234'
                    });
                    return;
                }
                if (selectedFiles.length < 2) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Minimal upload 2 foto hasil pekerjaan',
                        confirmButtonColor: '#7a5234'
                    });
                    return;
                }
                if (selectedFiles.length > 5) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Maksimal 5 foto hasil pekerjaan',
                        confirmButtonColor: '#7a5234'
                    });
                    return;
                }
                for (let f of selectedFiles) {
                    if (f.size > 4 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'warning',
                            text: 'Ukuran foto maksimal 4MB: ' + f.name,
                            confirmButtonColor: '#7a5234'
                        });
                        return;
                    }
                }
                Swal.fire({
                    title: "Kirim Laporan?",
                    text: "Laporan akan dikirim untuk persetujuan atasan dan administrator.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#7a5234",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Kirim",
                    cancelButtonText: "Batal"
                }).then((r) => {
                    if (r.isConfirmed) document.getElementById('form_laporan').submit();
                });
            });
        });
    </script>
@endpush
