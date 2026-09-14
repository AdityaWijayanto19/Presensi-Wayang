@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/wfh" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Pengajuan Work From Home</div>
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

            <form method="POST" action="/wfh/store" id="form_wfh" autocomplete="off">
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

                <div class="mb-4">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">
                        Tanggal WFH <span class="text-red-500">*</span>
                    </label>

                    <!-- Wrapper relatif untuk mengunci posisi ikon -->
                    <div class="relative flex items-center">
                        <!-- Ikon di-position absolute di dalam input -->
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 z-10">
                            <i data-lucide="calendar-clock" style="width:20px;height:20px;"></i>
                        </div>

                        <input type="text"
                            class="w-full pl-10 pr-3 py-2 text-sm bg-white border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            name="tgl_wfh" id="tgl_wfh" placeholder="Pilih Tanggal WFH" autocomplete="off" required>
                    </div>

                    <!-- Teks Helper -->
                    <div class="mt-1">
                        @if ($disableToday)
                            <small class="text-[11px] text-red-500 block">
                                Hari ini sudah lewat jam masuk, minimal 15 menit sebelum jam masuk.
                            </small>
                        @else
                            <small class="text-[11px] text-[#a8a29e] block">
                                Minimal H+1, tidak bisa hari ini jika sudah lewat jam masuk.
                            </small>
                        @endif
                    </div>
                </div>

                {{-- Keterangan WFH / Alasan WFH --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Keterangan WFH / Alasan WFH <span
                            class="text-red-500">*</span></label>
                    <textarea name="keterangan" id="keterangan" rows="3" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Jelaskan alasan mengapa harus WFH hari ini..." required>{{ old('keterangan') }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]">Contoh: kondisi kesehatan, jarak tempuh jauh, dll.</small>
                </div>

                {{-- Deskripsi Pekerjaan --}}
                <div class="form-group mt-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Deskripsi Pekerjaan <span
                            class="text-red-500">*</span></label>
                    <textarea name="deskripsi_pekerjaan" id="deskripsi_pekerjaan" rows="5" maxlength="2000" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="1. Menuliskan list pekerjaan&#10;2. List pekerjaan dibuat numerik/berurutan&#10;3. Dokumentasikan hasil kerja untuk laporan" required>{{ old('deskripsi_pekerjaan') }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]"><span id="charCount">0</span>/2000 karakter • Maksimal 10
                        poin</small>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mt-3 flex gap-2.5">
                    <i data-lucide="info"
                        class="text-amber-600 shrink-0 mt-0.5" style="width:18px;height:18px;"></i>
                    <p class="text-[11px] leading-relaxed text-amber-800">Setelah submit, Surat WFH menunggu
                        persetujuan.
                        Setelah disetujui, kamu bisa input laporan setelah absen pulang.</p>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="send" style="margin-right:6px;"></i> Ajukan WFH
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('myscript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ── Server Time (source of truth) ────────────────────
            var SERVER_TODAY = '{{ now("Asia/Jakarta")->format("Y-m-d") }}';
            var SERVER_DISABLE_TODAY = {{ $disableToday ? 'true' : 'false' }};

            // ── Flatpickr Initialization ────────────────────────
            var minDateSetting = SERVER_DISABLE_TODAY
                ? '{{ now("Asia/Jakarta")->addDay()->format("Y-m-d") }}'
                : SERVER_TODAY;

            flatpickr("#tgl_wfh", {
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

            var el = document.getElementById('deskripsi_pekerjaan');
            var MAX = 10;

            // ── Helpers ──────────────────────────────────────────

            // Cari nomor terbesar dari semua baris yang punya prefix "N. "
            function maxLineNumber(text) {
                var max = 0;
                text.split("\n").forEach(function(l) {
                    var m = l.match(/^(\d+)\.\s/);
                    if (m) max = Math.max(max, parseInt(m[1]));
                });
                return max;
            }

            // Renumber semua baris (dipanggil saat line dihapus)
            function renumber(text) {
                var lines = text.split("\n");
                var out = [],
                    num = 1;
                for (var i = 0; i < lines.length; i++) {
                    var content = lines[i].replace(/^\d+[\.\s]*/, "");
                    if (content === "" && i === lines.length - 1) {
                        out.push(""); // trailing empty
                    } else if (content === "") {
                        continue; // skip baris kosong di tengah
                    } else {
                        out.push(num + ". " + content);
                        num++;
                    }
                }
                return out.join("\n");
            }

            // ── Focus ────────────────────────────────────────────

            el.addEventListener("focus", function() {
                if (this.value === "") {
                    this.value = "1. ";
                }
            });

            // ── Keydown: Enter + Backspace ───────────────────────

            el.addEventListener("keydown", function(e) {
                var val = this.value;
                var pos = this.selectionStart;

                // ═══════════════ ENTER ═══════════════
                if (e.keyCode === 13) {
                    e.preventDefault();
                    var lines = val.split("\n");
                    var trailing = lines[lines.length - 1] === "";
                    var totalLines = trailing ? lines.length - 1 : lines.length;
                    if (totalLines >= MAX) return;

                    var nextNum = maxLineNumber(val) + 1;

                    // Hapus trailing newline dulu supaya gak double
                    var cleanVal = trailing ? val.substring(0, val.length - 1) : val;
                    var cleanPos = Math.min(pos, cleanVal.length);
                    var before = cleanVal.substring(0, cleanPos);
                    var after = cleanVal.substring(cleanPos);

                    this.value = before + "\n" + nextNum + ". " + after;
                    var newPos = before.length + 1 + String(nextNum).length + 2;
                    this.setSelectionRange(newPos, newPos);
                    return;
                }

                // ═══════════════ BACKSPACE ═══════════════
                if (e.keyCode === 8 && pos > 0) {
                    var beforeCursor = val.substring(0, pos);
                    var lines = val.split("\n");
                    var lineIdx = beforeCursor.split("\n").length - 1;
                    var currentLine = lines[lineIdx];

                    // Case A: baris cuma berisi prefix "N. " atau "N." → hapus seluruh baris
                    if (/^\d+\.?\s?$/.test(currentLine)) {
                        e.preventDefault();

                        // Jika cuma 1 baris → clear semua
                        if (lines.length <= 1) {
                            this.value = "";
                            this.setSelectionRange(0, 0);
                            return;
                        }

                        // Hapus baris, renumber sisa
                        lines.splice(lineIdx, 1);
                        var newVal = renumber(lines.join("\n"));
                        this.value = newVal;

                        // Cursor ke akhir baris sebelumnya
                        var targetIdx = Math.max(0, lineIdx - 1);
                        var newLines = newVal.split("\n");
                        var cursorPos = 0;
                        for (var i = 0; i < targetIdx; i++) cursorPos += newLines[i].length + 1;
                        cursorPos += newLines[targetIdx].length;
                        this.setSelectionRange(cursorPos, cursorPos);
                        return;
                    }

                    // Case B: cursor di awal baris (posisi = awal baris) dan baris punya prefix
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
                        var newVal = renumber(lines.join("\n"));
                        this.value = newVal;

                        // Cursor di akhir prevLine content (sebelum content dari line yang dihapus)
                        var newLines = newVal.split("\n");
                        var cursorPos = 0;
                        for (var i = 0; i < prevLineIdx; i++) cursorPos += newLines[i].length + 1;
                        cursorPos += prevLine.length;
                        this.setSelectionRange(cursorPos, cursorPos);
                        return;
                    }
                }
            });

            // ── Input: HANYA update char count ───────────────────

            el.addEventListener("input", function() {
                document.getElementById('charCount').textContent = this.value.length;
            });

            // ── Init ─────────────────────────────────────────────

            document.getElementById('charCount').textContent = el.value.length;

            // ── Submit validation ────────────────────────────────

            document.getElementById('form_wfh').addEventListener('submit', function(e) {
                e.preventDefault();
                var tgl = document.getElementById('tgl_wfh').value;
                var keterangan = document.getElementById('keterangan').value.trim();
                var desk = el.value.trim();
                if (!tgl) {
                    Swal.fire({
                        icon: "warning",
                        text: "Tanggal WFH harus diisi",
                        confirmButtonColor: "#7a5234"
                    });
                    return;
                }
                if (!keterangan || keterangan.length < 5) {
                    Swal.fire({
                        icon: "warning",
                        text: "Keterangan WFH minimal 5 karakter",
                        confirmButtonColor: "#7a5234"
                    });
                    return;
                }
                if (!desk || desk.length < 10) {
                    Swal.fire({
                        icon: "warning",
                        text: "Deskripsi minimal 10 karakter",
                        confirmButtonColor: "#7a5234"
                    });
                    return;
                }
                Swal.fire({
                    title: "Ajukan WFH?",
                    text: "Data akan digenerate jadi PDF dan dikirim untuk persetujuan.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#7a5234",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Ajukan",
                    cancelButtonText: "Batal"
                }).then(function(r) {
                    if (r.isConfirmed) document.getElementById('form_wfh').submit();
                });
            });
        });
    </script>
@endpush
