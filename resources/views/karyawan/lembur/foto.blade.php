@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-coklat text-light">
        <div class="left">
            <a href="/lembur" class="headerButton goBack">
                <i data-lucide="chevron-left"></i>
            </a>
        </div>
        <div class="pageTitle">Foto Lembur</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    @php
        $messagesuccess = Session::get('success');
        $messageerror = Session::get('error');
        $lembur = $data->lembur;
        $hasMulai = $data->has_mulai;
        $hasSelesai = $data->has_selesai;
        $tglLembur = $lembur->tgl_lembur instanceof \Carbon\Carbon ? $lembur->tgl_lembur->format('d M Y') : $lembur->tgl_lembur;
    @endphp

    <div class="flex mt-[70px]">
        <div class="w-full px-3">
            @if ($messagesuccess)
                <div class="flex items-center gap-2.5 bg-[#ecfdf5] border border-[#a7f3d0] text-[#065f46] text-[13px] font-medium rounded-xl py-2.5 px-3.5" id="alert-success">
                    <i data-lucide="circle-check" class="text-[#10b981] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messagesuccess }}</span>
                </div>
            @endif
            @if ($messageerror)
                <div class="flex items-center gap-2.5 bg-[#fef2f2] border border-[#fecaca] text-[#991b1b] text-[13px] font-medium rounded-xl py-2.5 px-3.5">
                    <i data-lucide="circle-alert" class="text-[#ef4444] shrink-0" style="width:18px;height:18px;"></i>
                    <span class="flex-1 leading-tight">{{ $messageerror }}</span>
                </div>
            @endif

            <div class="mt-3">
                <h3 class="text-[14px] font-bold text-[#1c1917]">Lembur {{ $tglLembur }}</h3>
                <p class="text-[12px] text-[#78716c] mt-0.5">{{ $lembur->keterangan ?? '-' }}</p>
            </div>

            {{-- Status Steps --}}
            <div class="flex items-center gap-2 mt-4">
                <div class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-full {{ $hasMulai ? 'bg-emerald-500' : 'bg-gray-300' }} flex items-center justify-center">
                        @if ($hasMulai)
                            <i data-lucide="check" class="text-white" style="width:14px;height:14px;"></i>
                        @else
                            <span class="text-white text-[10px] font-bold">1</span>
                        @endif
                    </div>
                    <span class="text-[11px] {{ $hasMulai ? 'text-emerald-600 font-medium' : 'text-[#78716c]' }}">Foto Mulai</span>
                </div>
                <div class="flex-1 h-0.5 {{ $hasMulai ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>
                <div class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-full {{ $hasSelesai ? 'bg-emerald-500' : 'bg-gray-300' }} flex items-center justify-center">
                        @if ($hasSelesai)
                            <i data-lucide="check" class="text-white" style="width:14px;height:14px;"></i>
                        @else
                            <span class="text-white text-[10px] font-bold">2</span>
                        @endif
                    </div>
                    <span class="text-[11px] {{ $hasSelesai ? 'text-emerald-600 font-medium' : 'text-[#78716c]' }}">Foto Selesai</span>
                </div>
            </div>

            {{-- Webcam Section --}}
            <div class="mt-4 bg-white rounded-xl border border-[#f0ece8] overflow-hidden">
                <div class="relative bg-black" style="aspect-ratio: 9/16;">
                    <video id="webcam" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden"></canvas>

                    {{-- Overlay --}}
                    <div class="absolute bottom-2 left-2 right-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded">
                        <div class="font-bold">LEMBUR</div>
                        <div id="clock">{{ now('Asia/Jakarta')->format('H:i:s') }}</div>
                        <div>{{ $data->karyawan->nama_lengkap }}</div>
                        <div>{{ $data->karyawan->nik }}</div>
                    </div>
                </div>

                <div class="p-3">
                    @if (!$hasMulai)
                        <button id="btnCapture" class="w-full py-2.5 bg-blue-500 text-white text-[13px] font-semibold rounded-lg">
                            <i data-lucide="camera" class="inline" style="width:16px;height:16px;"></i> Ambil Foto Mulai Lembur
                        </button>
                    @elseif (!$hasSelesai)
                        <button id="btnCapture" class="w-full py-2.5 bg-orange-500 text-white text-[13px] font-semibold rounded-lg">
                            <i data-lucide="camera" class="inline" style="width:16px;height:16px;"></i> Ambil Foto Selesai Lembur
                        </button>
                    @else
                        <div class="text-center py-3">
                            <div class="text-emerald-500 text-[13px] font-semibold">
                                <i data-lucide="check-circle" class="inline" style="width:16px;height:16px;"></i> Foto lengkap!
                            </div>
                            <a href="/lembur/{{ $lembur->id }}/laporan" class="mt-2 inline-block px-4 py-2 bg-purple-500 text-white text-[13px] font-semibold rounded-lg">
                                Isi Laporan →
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Preview --}}
            <div id="preview-section" class="mt-4 hidden">
                <h4 class="text-[13px] font-bold text-[#1c1917] mb-2">Preview Foto</h4>
                <img id="previewImg" class="w-full rounded-lg border border-[#f0ece8]" alt="Preview">
                <div class="flex gap-2 mt-2">
                    <button id="btnRetake" class="flex-1 py-2 bg-gray-200 text-[#1c1917] text-[13px] font-medium rounded-lg">Ulangi</button>
                    <button id="btnConfirm" class="flex-1 py-2 bg-emerald-500 text-white text-[13px] font-semibold rounded-lg">Simpan</button>
                </div>
            </div>

        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const btnCapture = document.getElementById('btnCapture');
        const previewSection = document.getElementById('preview-section');
        const previewImg = document.getElementById('previewImg');
        const btnRetake = document.getElementById('btnRetake');
        const btnConfirm = document.getElementById('btnConfirm');
        let currentBlob = null;

        // Clock
        setInterval(() => {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID', { hour12: false, timeZone: 'Asia/Jakarta' });
        }, 1000);

        // Start webcam
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 1280 } } })
            .then(stream => { video.srcObject = stream; })
            .catch(err => {
                console.error('Webcam error:', err);
                alert('Tidak bisa mengakses kamera. Pastikan izin kamera diberikan.');
            });

        btnCapture.addEventListener('click', function() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);
            canvas.toBlob(function(blob) {
                currentBlob = blob;
                const url = URL.createObjectURL(blob);
                previewImg.src = url;
                previewSection.classList.remove('hidden');
                btnCapture.classList.add('hidden');
            }, 'image/jpeg', 0.95);
        });

        btnRetake.addEventListener('click', function() {
            previewSection.classList.add('hidden');
            btnCapture.classList.remove('hidden');
            currentBlob = null;
        });

        btnConfirm.addEventListener('click', function() {
            if (!currentBlob) return;

            const type = {!! $hasMulai ? "'selesai'" : "'mulai'" !!};

            const reader = new FileReader();
            reader.onload = function() {
                const base64 = reader.result.split(',')[1];

                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lokasi = pos.coords.latitude + ',' + pos.coords.longitude;
                    sendFoto(type, base64, lokasi);
                }, function() {
                    sendFoto(type, base64, '');
                }, { enableHighAccuracy: true, timeout: 10000 });
            };
            reader.readAsDataURL(currentBlob);
        });

        function sendFoto(type, image, lokasi) {
            const lemburId = {!! $lembur->id !!};

            fetch('/lembur/' + lemburId + '/foto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ type: type, image: image, lokasi: lokasi })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan foto');
                    previewSection.classList.add('hidden');
                    btnCapture.classList.remove('hidden');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                previewSection.classList.add('hidden');
                btnCapture.classList.remove('hidden');
            });
        }

        setTimeout(function () {
            let alert = document.getElementById('alert-success');
            if (alert) { alert.style.opacity = '0'; alert.style.transition = 'opacity 0.3s'; setTimeout(() => alert.style.display = 'none', 300); }
        }, 3000);
    </script>
@endsection
