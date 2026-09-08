@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/wfh" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Input Laporan WFH</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $karyawan = Auth::guard('karyawan')->user();
    @endphp

    <div class="flex mt-[70px]">
        <div class="w-full px-3">

            @if(Session::get('success'))
                <div class="bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">{{ Session::get('success') }}</div>
            @endif
            @if(Session::get('error'))
                <div class="bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">{{ Session::get('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] rounded-xl py-2.5 px-3.5 mb-3">
                    <ul class="mb-0 list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="/wfh/{{ $wfh->id }}/laporan" enctype="multipart/form-data" id="form_laporan">
                @csrf

                {{-- Info Compact: Pengaju + Tanggal + Lokasi --}}
                <div class="bg-white rounded-2xl border border-[#f0ece8] p-4 mb-3">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-[#fdf8f4] border border-[#f0ece8] flex items-center justify-center text-coklat">
                            <i data-lucide="user" style="width:18px;height:18px;"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Pengaju</div>
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ $karyawan->nama_lengkap }}</div>
                            <div class="text-[12px] text-[#78716c]">{{ $karyawan->posisi }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-[#f0f9ff] border border-[#bae6fd] flex items-center justify-center text-sky-600">
                            <i data-lucide="calendar" style="width:18px;height:18px;"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Tanggal WFH</div>
                            <div class="text-[14px] font-bold text-[#1c1917]">{{ date('d M Y', strtotime($wfh->tgl_wfh)) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#f0fdf4] border border-[#bbf7d0] flex items-center justify-center text-green-600">
                            <i data-lucide="map-pin" style="width:18px;height:18px;"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-semibold tracking-wide text-[#a8a29e] uppercase">Lokasi Absen Masuk</div>
                            <div class="text-[13px] font-bold text-[#1c1917]">{{ $liveLocation }}</div>
                        </div>
                    </div>
                </div>

                {{-- Detail Hasil Pekerjaan (Deskripsi) --}}
                <div class="form-group mb-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Deskripsi Hasil Pekerjaan <span class="text-red-500">*</span></label>
                    <textarea name="laporan_deskripsi" id="deskripsi_laporan" rows="5" class="form-control" placeholder="1. Menuliskan list pekerjaan&#10;2. List pekerjaan dibuat numerik/berurutan&#10;3. Dokumentasikan hasil kerja" required>{{ old('laporan_deskripsi', $wfh->laporan_deskripsi) }}</textarea>
                    <small class="text-[11px] text-[#a8a29e]"><span id="charCountDesk">0</span>/3000 karakter &bull; Maksimal 10 poin</small>
                </div>

                {{-- Upload Gambar Hasil Pekerjaan (Min 2, Max 5) --}}
                <div class="form-group mb-3">
                    <label class="text-[12px] font-semibold text-[#44403c] mb-1 block">Foto Hasil Pekerjaan <span class="text-red-500">*</span></label>
                    <input type="file" name="laporan_images[]" id="laporan_images" class="form-control" accept="image/*" multiple>
                    <small class="text-[11px] text-[#a8a29e]">Minimal 2 foto, maksimal 5 foto. Format: JPG, JPEG, PNG. Maks 4MB per foto.</small>
                    <div id="image-preview" class="flex flex-wrap gap-2 mt-2"></div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="circle-check" style="margin-right:6px;"></i> Kirim Laporan
                    </button>
                    <p class="text-[11px] text-[#a8a29e] text-center mt-2">Laporan akan melalui persetujuan atasan dan administrator.</p>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('myscript')
<style>
    .preview-thumb { position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; border: 1.5px solid #f0ece8; cursor: pointer; flex-shrink: 0; transition: border-color 0.15s; }
    .preview-thumb:hover { border-color: #7a5234; }
    .preview-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .preview-thumb .btn-remove {
        position: absolute; top: 3px; right: 3px; width: 20px; height: 20px; border-radius: 999px;
        background: rgba(220,38,38,0.9); border: 1.5px solid rgba(255,255,255,0.5); color: #fff;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        font-size: 13px; line-height: 1; font-weight: 700; z-index: 2; transition: background 0.15s;
    }
    .preview-thumb .btn-remove:hover { background: #dc2626; }
    .img-preview-modal { position: fixed; inset: 0; z-index: 99999; background: rgba(20,12,6,0.7); backdrop-filter: blur(6px);
        display: none; align-items: center; justify-content: center; padding: 16px; }
    .img-preview-modal.open { display: flex; }
    .img-preview-modal img { max-width: 92vw; max-height: 85vh; object-fit: contain; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
    .img-preview-modal .btn-close-preview {
        position: absolute; top: 16px; right: 16px; width: 40px; height: 40px; border-radius: 999px;
        background: rgba(255,255,255,0.9); border: none; color: #44403c; font-size: 22px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
</style>

<div id="imgPreviewModal" class="img-preview-modal">
    <button type="button" class="btn-close-preview" id="imgPreviewClose">&times;</button>
    <img id="imgPreviewEl" src="" alt="Preview">
</div>

<script>
$(function(){
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
        selectedFiles.forEach(function(file, idx){
            const reader = new FileReader();
            reader.onload = function(e){
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
                btn.addEventListener('click', function(ev){
                    ev.stopPropagation();
                    selectedFiles.splice(idx, 1);
                    syncInput();
                    renderPreviews();
                    fileInput.setCustomValidity(selectedFiles.length === 0 ? 'required' : '');
                });
                div.appendChild(btn);

                div.addEventListener('click', function(){
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
    imgPreviewModal.addEventListener('click', function(e){ if(e.target === imgPreviewModal) closeImgPreview(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && imgPreviewModal.classList.contains('open')) closeImgPreview(); });

    fileInput.addEventListener('change', function(){
        const incoming = Array.from(this.files);
        const rejected = [];

        incoming.forEach(function(file){
            if(file.size > 4*1024*1024){
                rejected.push(file.name);
                return;
            }
            if(selectedFiles.length >= 5) return;
            selectedFiles.push(file);
        });

        if(rejected.length){
            Swal.fire({icon:'warning', text:'Ukuran foto maksimal 4MB: ' + rejected.join(', '), confirmButtonColor:'#7a5234'});
        }
        if(selectedFiles.length > 5){
            selectedFiles = selectedFiles.slice(0, 5);
            Swal.fire({icon:'warning', text:'Maksimal 5 foto hasil pekerjaan', confirmButtonColor:'#7a5234'});
        }

        syncInput();
        renderPreviews();
        fileInput.setCustomValidity('');
    });

    // ── Deskripsi Auto-Numbering ──────────────────────────
    var $desk = $('#deskripsi_laporan');
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

    $desk.on("focus", function() {
        if (this.value === "") this.value = "1. ";
    });

    $desk.on("keydown", function(e) {
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

    $desk.on("input", function() {
        $("#charCountDesk").text(this.value.length);
    });

    $("#charCountDesk").text($desk.val().length);

    $('#form_laporan').submit(function(e){
        e.preventDefault();
        const desk = $('textarea[name="laporan_deskripsi"]').val().trim();
        if(!desk || desk.length < 10){
            Swal.fire({icon:'warning', text:'Deskripsi minimal 10 karakter', confirmButtonColor:'#7a5234'});
            return;
        }
        if(selectedFiles.length < 2){
            Swal.fire({icon:'warning', text:'Minimal upload 2 foto hasil pekerjaan', confirmButtonColor:'#7a5234'});
            return;
        }
        if(selectedFiles.length > 5){
            Swal.fire({icon:'warning', text:'Maksimal 5 foto hasil pekerjaan', confirmButtonColor:'#7a5234'});
            return;
        }
        for(let f of selectedFiles){
            if(f.size > 4*1024*1024){
                Swal.fire({icon:'warning', text:'Ukuran foto maksimal 4MB: ' + f.name, confirmButtonColor:'#7a5234'});
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
        }).then((r)=>{ if(r.isConfirmed) $('#form_laporan')[0].submit(); });
    });
});
</script>
@endpush
