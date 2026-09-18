@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/lembur/{{ $data->lembur->id }}/foto" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Laporan Lembur</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $messagesuccess = Session::get('success');
        $messageerror = Session::get('error');
        $lembur = $data->lembur;
        $tglLembur = $lembur->tgl_lembur instanceof \Carbon\Carbon ? $lembur->tgl_lembur->format('d M Y') : $lembur->tgl_lembur;
    @endphp

    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if ($messagesuccess)
                <div class="bg-[#34c759] text-white border border-[#34c759] text-[13px] rounded-md py-1.5 px-4">{{ $messagesuccess }}</div>
            @endif
            @if ($messageerror)
                <div class="bg-[#ec4433] text-white border border-[#ec4433] text-[13px] rounded-md py-1.5 px-4">{{ $messageerror }}</div>
            @endif

            {{-- Info Lembur --}}
            <div class="bg-white rounded-xl border border-[#f0ece8] p-4 mt-2">
                <div class="text-[13px] font-bold text-[#1c1917]">Info Lembur</div>
                <div class="mt-2 space-y-1">
                    <div class="flex justify-between text-[12px]">
                        <span class="text-[#78716c]">Tanggal</span>
                        <span class="font-medium text-[#1c1917]">{{ $tglLembur }}</span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-[#78716c]">Durasi</span>
                        <span class="font-medium text-[#1c1917]">{{ $data->durasi_formatted }}</span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <span class="text-[#78716c]">Keterangan</span>
                        <span class="font-medium text-[#1c1917] text-right max-w-[200px]">{{ $lembur->keterangan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Foto Mulai & Selesai --}}
            <div class="grid grid-cols-2 gap-2 mt-2">
                @if (!empty($lembur->foto_mulai))
                    <div class="bg-white rounded-xl border border-[#f0ece8] p-2 text-center">
                        <div class="text-[10px] text-[#78716c] mb-1">Foto Mulai</div>
                        <img src="/presensi/showfilelembur/{{ $lembur->foto_mulai }}" class="w-full h-24 object-cover rounded-lg" alt="Mulai">
                        @if ($lembur->waktu_mulai)
                            <div class="text-[10px] text-[#a8a29e] mt-1">{{ $lembur->waktu_mulai->format('H:i') }} WIB</div>
                        @endif
                    </div>
                @endif
                @if (!empty($lembur->foto_selesai))
                    <div class="bg-white rounded-xl border border-[#f0ece8] p-2 text-center">
                        <div class="text-[10px] text-[#78716c] mb-1">Foto Selesai</div>
                        <img src="/presensi/showfilelembur/{{ $lembur->foto_selesai }}" class="w-full h-24 object-cover rounded-lg" alt="Selesai">
                        @if ($lembur->waktu_selesai)
                            <div class="text-[10px] text-[#a8a29e] mt-1">{{ $lembur->waktu_selesai->format('H:i') }} WIB</div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Form Laporan --}}
            <form method="POST" action="/lembur/{{ $lembur->id }}/laporan" id="form_laporan" autocomplete="off" enctype="multipart/form-data" class="mt-3">
                @csrf

                {{-- Deskripsi Pekerjaan --}}
                <div class="form-group">
                    <label class="text-sm font-medium text-[#1c1917]">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
                    <textarea name="deskripsi_pekerjaan" id="deskripsi_pekerjaan" rows="5" maxlength="3000"
                              class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                              placeholder="Jelaskan pekerjaan yang dilakukan saat lembur..." required minlength="10">{{ old('deskripsi_pekerjaan') }}</textarea>
                    <small class="text-[#a8a29e] text-[11px]">Min. 10 karakter, maks. 3000 karakter.</small>
                </div>

                {{-- Upload Gambar --}}
                <div class="form-group mt-2">
                    <label class="text-sm font-medium text-[#1c1917]">Foto Hasil Kerja <span class="text-red-500">*</span></label>
                    <small class="text-red-500 block -mt-1 mb-2">* Minimal 2 foto, maksimal 5 foto. Format: JPG, JPEG, PNG (Maks. 4 MB per foto)</small>
                    <input type="file" name="laporan_images[]" id="laporan_images" multiple accept="image/jpg,image/jpeg,image/png"
                           class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                {{-- Preview Gambar --}}
                <div id="image-preview" class="grid grid-cols-3 gap-2 mt-2"></div>

                {{-- Submit --}}
                <div class="form-group mt-3">
                    <button class="btn btn-primary w-full">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ── Auto-numbering deskripsi pekerjaan ──
        var el = document.getElementById('deskripsi_pekerjaan');
        var MAX = 10;

        function maxLineNumber(text) {
            var max = 0;
            text.split("\n").forEach(function(l) {
                var m = l.match(/^(\d+)\.\s/);
                if (m) max = Math.max(max, parseInt(m[1]));
            });
            return max;
        }

        function renumber(text) {
            var lines = text.split("\n");
            var out = [], num = 1;
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

        el.addEventListener("focus", function() {
            if (this.value === "") {
                this.value = "1. ";
            }
        });

        el.addEventListener("keydown", function(e) {
            var val = this.value;
            var pos = this.selectionStart;

            // ENTER
            if (e.keyCode === 13) {
                e.preventDefault();
                var lines = val.split("\n");
                var trailing = lines[lines.length - 1] === "";
                var totalLines = trailing ? lines.length - 1 : lines.length;
                if (totalLines >= MAX) return;

                var nextNum = maxLineNumber(val) + 1;
                var cleanVal = trailing ? val.substring(0, val.length - 1) : val;
                var cleanPos = Math.min(pos, cleanVal.length);
                var before = cleanVal.substring(0, cleanPos);
                var after = cleanVal.substring(cleanPos);

                this.value = before + "\n" + nextNum + ". " + after;
                var newPos = before.length + 1 + String(nextNum).length + 2;
                this.setSelectionRange(newPos, newPos);
                return;
            }

            // BACKSPACE
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
                    var newVal = renumber(lines.join("\n"));
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
                    var newVal = renumber(lines.join("\n"));
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

        // ── Image preview ──
        const input = document.getElementById('laporan_images');
        const preview = document.getElementById('image-preview');
        let files = [];

        input.addEventListener('change', function() {
            const newFiles = Array.from(this.files);
            files = [...files, ...newFiles].slice(0, 5);
            renderPreview();
            updateInput();
        });

        function renderPreview() {
            preview.innerHTML = '';
            files.forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg border border-[#f0ece8]" alt="Preview">
                        <button type="button" data-idx="${idx}" class="remove-img absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-[10px] flex items-center justify-center">✕</button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });

            document.querySelectorAll('.remove-img').forEach(btn => {
                btn.addEventListener('click', function() {
                    files.splice(parseInt(this.dataset.idx), 1);
                    renderPreview();
                    updateInput();
                });
            });
        }

        function updateInput() {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            input.files = dt.files;
        }

        document.getElementById('form_laporan').addEventListener('submit', function(e) {
            e.preventDefault();

            var deskripsi = document.getElementById('deskripsi_pekerjaan').value.trim();
            if (deskripsi.length < 10) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Deskripsi pekerjaan minimal 10 karakter!', confirmButtonColor: '#7a5234' });
                return false;
            }

            if (files.length < 2) {
                Swal.fire({ title: 'Error!', icon: 'warning', text: 'Foto hasil kerja minimal 2 foto!', confirmButtonColor: '#7a5234' });
                return false;
            }

            for (let f of files) {
                if (f.size > 4 * 1024 * 1024) {
                    Swal.fire({ title: 'Error!', icon: 'warning', text: 'Ukuran foto maksimal 4MB!', confirmButtonColor: '#7a5234' });
                    return false;
                }
            }

            Swal.fire({
                title: 'Kirim Laporan Lembur?',
                text: 'Pastikan data yang dikirim sudah benar!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7a5234',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { document.getElementById('form_laporan').submit(); }
            });
        });
    });
</script>
@endpush
