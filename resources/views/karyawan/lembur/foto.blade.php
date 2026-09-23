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
        $rencanaWaktu = $lembur->rencana_waktu;
        $durasiFormatted = $lembur->durasi_formatted;
        $isPreShift = $lembur->is_pre_shift;
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

            {{-- Status Steps (Small, Top) --}}
            <div class="flex items-center justify-center gap-1.5 mt-2">
                <div class="flex items-center gap-1">
                    <div class="w-5 h-5 rounded-full {{ $hasMulai ? 'bg-emerald-500' : 'bg-gray-300' }} flex items-center justify-center">
                        @if ($hasMulai)
                            <i data-lucide="check" class="text-white" style="width:12px;height:12px;"></i>
                        @else
                            <span class="text-white text-[9px] font-bold">1</span>
                        @endif
                    </div>
                    <span class="text-[10px] {{ $hasMulai ? 'text-emerald-600 font-medium' : 'text-[#78716c]' }} hidden sm:inline">Mulai</span>
                </div>
                <div class="w-8 h-0.5 {{ $hasMulai ? 'bg-emerald-300' : 'bg-gray-200' }} hidden sm:block"></div>
                <div class="flex items-center gap-1">
                    <div class="w-5 h-5 rounded-full {{ $hasSelesai ? 'bg-emerald-500' : 'bg-gray-300' }} flex items-center justify-center">
                        @if ($hasSelesai)
                            <i data-lucide="check" class="text-white" style="width:12px;height:12px;"></i>
                        @else
                            <span class="text-white text-[9px] font-bold">2</span>
                        @endif
                    </div>
                    <span class="text-[10px] {{ $hasSelesai ? 'text-emerald-600 font-medium' : 'text-[#78716c]' }} hidden sm:inline">Selesai</span>
                </div>
            </div>

            {{-- Info Rencana Waktu --}}
            @if ($rencanaWaktu)
                <div class="mt-2 bg-blue-50 border border-blue-200 rounded-xl px-3 py-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="clock" class="text-blue-500 shrink-0" style="width:16px;height:16px;"></i>
                        <div>
                            <span class="text-[12px] text-blue-700 font-semibold">Rencana: {{ $rencanaWaktu }}</span>
                            <span class="text-[11px] text-blue-500">({{ $durasiFormatted }})</span>
                        </div>
                    </div>
                    @if ($isPreShift)
                        <div class="mt-1 text-[10px] text-blue-600 flex items-center gap-1">
                            <i data-lucide="info" style="width:12px;height:12px;"></i>
                            Lembur sebelum jam masuk (pre-shift)
                        </div>
                    @endif
                </div>
            @endif

            {{-- Webcam Section --}}
            <div class="mt-3 bg-white rounded-xl border border-[#f0ece8] overflow-hidden">
                <div class="relative bg-black" style="aspect-ratio: 4/5;">
                    <video id="webcam" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden"></canvas>

                    {{-- Camera Switch Button --}}
                    <button type="button" id="btnSwitchCamera" class="absolute top-1.5 right-1.5 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm shadow-md flex items-center justify-center text-coklat hover:bg-white transition-colors" aria-label="Ganti Kamera">
                        <i data-lucide="rotate-ccw" style="width:18px;height:18px;"></i>
                    </button>

                    {{-- Overlay Card (Compact, Text-only BG) --}}
                    <div class="absolute bottom-1.5 left-1.5 z-10">
                        <div class="flex flex-col gap-1.5">
                            {{-- Line 1: Lembur + Date + Clock --}}
                            <div class="flex items-center gap-1.5">
                                <span class="bg-coklat/90 text-white text-[10px] font-semibold px-2 py-0.5 rounded">Lembur</span>
                                <span class="bg-white/90 text-[#1c1917] text-[10px] font-medium px-2 py-0.5 rounded">{{ $tglLembur }}</span>
                                <span id="clock" class="bg-white/90 text-[#1c1917] text-[12px] font-bold font-mono tabular-nums px-2 py-0.5 rounded">{{ now('Asia/Jakarta')->format('H:i:s') }}</span>
                            </div>

                            {{-- Line 2: Nama + NIK --}}
                            <div class="flex items-center gap-1.5">
                                <span class="bg-white/90 text-[#78716c] text-[9px] font-medium px-2 py-0.5 rounded">Nama</span>
                                <span class="bg-white/90 text-[#1c1917] text-[10px] font-semibold px-2 py-0.5 rounded truncate max-w-[140px]">{{ $data->karyawan->nama_lengkap }}</span>
                                <span class="bg-white/90 text-[#78716c] text-[9px] font-medium px-2 py-0.5 rounded">NIK</span>
                                <span class="bg-white/90 text-[#1c1917] text-[10px] font-semibold font-mono px-2 py-0.5 rounded">{{ $data->karyawan->nik }}</span>
                            </div>

                            {{-- Line 3: Location --}}
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="text-white" style="width:11px;height:11px;"></i>
                                <span class="bg-white/90 text-[#78716c] text-[9px] font-medium px-2 py-0.5 rounded">Lokasi otomatis</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-2.5">
                    @if (!$hasMulai)
                        <button id="btnCapture" class="w-full py-2.5 bg-blue-500 text-white text-[12px] font-semibold rounded-lg flex items-center justify-center gap-2">
                            <i data-lucide="camera" class="inline" style="width:16px;height:16px;"></i> Ambil Foto Mulai Lembur
                        </button>
                    @elseif (!$hasSelesai)
                        <button id="btnCapture" class="w-full py-2.5 bg-orange-500 text-white text-[12px] font-semibold rounded-lg flex items-center justify-center gap-2">
                            <i data-lucide="camera" class="inline" style="width:16px;height:16px;"></i> Ambil Foto Selesai Lembur
                        </button>
                    @else
                        <div class="text-center py-2.5">
                            <div class="text-emerald-500 text-[12px] font-semibold flex items-center justify-center gap-1">
                                <i data-lucide="check-circle" class="inline" style="width:14px;height:14px;"></i> Foto lengkap!
                            </div>
                            <a href="/lembur/{{ $lembur->id }}/laporan" class="mt-1.5 inline-block px-3 py-1.5 bg-purple-500 text-white text-[11px] font-semibold rounded-lg">
                                Isi Laporan &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Preview --}}
            <div id="preview-section" class="mt-3 hidden">
                <h4 class="text-[12px] font-bold text-[#1c1917] mb-1.5">Preview Foto</h4>
                <img id="previewImg" class="w-full rounded-lg border border-[#f0ece8]" alt="Preview">
                <div class="flex gap-1.5 mt-1.5">
                    <button id="btnRetake" class="flex-1 py-1.5 bg-gray-200 text-[#1c1917] text-[11px] font-medium rounded-lg">Ulangi</button>
                    <button id="btnConfirm" class="flex-1 py-1.5 bg-emerald-500 text-white text-[11px] font-semibold rounded-lg">Simpan</button>
                </div>
            </div>

        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const btnCapture = document.getElementById('btnCapture');
        const btnRetake = document.getElementById('btnRetake');
        const btnConfirm = document.getElementById('btnConfirm');
        const btnSwitchCamera = document.getElementById('btnSwitchCamera');
        const previewSection = document.getElementById('preview-section');
        const previewImg = document.getElementById('previewImg');
        let currentBlob = null;
        let currentStream = null;
        let facingMode = 'environment'; // Default: kamera belakang

        // Clock
        setInterval(() => {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID', { hour12: false, timeZone: 'Asia/Jakarta' });
        }, 1000);

        // Start webcam
        function startCamera(mode) {
            facingMode = mode;
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    facingMode: { ideal: facingMode },
                    width: { ideal: 1080 },
                    height: { ideal: 1440 }
                }
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    currentStream = stream;
                    video.srcObject = stream;
                    // Mirror preview for front camera, keep normal for back camera
                    video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
                })
                .catch(err => {
                    console.error('Webcam error:', err);
                    // Fallback to user if environment not available
                    if (mode === 'environment') {
                        startCamera('user');
                    } else {
                        alert('Tidak bisa mengakses kamera. Pastikan izin kamera diberikan.');
                    }
                });
        }

        // Initialize with back camera
        startCamera('environment');

        // Switch camera button
        btnSwitchCamera.addEventListener('click', function() {
            startCamera(facingMode === 'environment' ? 'user' : 'environment');
        });

        btnCapture.addEventListener('click', function() {
            // Crop to 4:5 portrait (more natural for ID photos)
            const videoAspect = video.videoWidth / video.videoHeight;
            const targetAspect = 4 / 5;
            let cropWidth, cropHeight, cropX, cropY;

            if (videoAspect > targetAspect) {
                // Video is wider than 4:5, crop sides
                cropHeight = video.videoHeight;
                cropWidth = video.videoHeight * targetAspect;
                cropX = (video.videoWidth - cropWidth) / 2;
                cropY = 0;
            } else {
                // Video is taller than 4:5, crop top/bottom
                cropWidth = video.videoWidth;
                cropHeight = video.videoWidth / targetAspect;
                cropX = 0;
                cropY = (video.videoHeight - cropHeight) / 2;
            }

            canvas.width = cropWidth;
            canvas.height = cropHeight;

            // Mirror the captured image if using front camera
            if (facingMode === 'user') {
                ctx.translate(cropWidth, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
                ctx.setTransform(1, 0, 0, 1, 0, 0); // Reset transform
            } else {
                ctx.drawImage(video, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
            }

            canvas.toBlob(function(blob) {
                currentBlob = blob;
                const url = URL.createObjectURL(blob);
                previewImg.src = url;
                previewImg.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
                previewSection.classList.remove('hidden');
                btnCapture.classList.add('hidden');
            }, 'image/jpeg', 0.92);
        });

        btnRetake.addEventListener('click', function() {
            previewSection.classList.add('hidden');
            btnCapture.classList.remove('hidden');
            currentBlob = null;
        });

        btnConfirm.addEventListener('click', function() {
            if (!currentBlob) return;

            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Menyimpan...';
            btnConfirm.classList.add('opacity-50', 'cursor-not-allowed');

            const type = {!! $hasMulai ? "'selesai'" : "'mulai'" !!};

            const reader = new FileReader();
            reader.onload = function() {
                const base64 = reader.result; // full data URL with prefix

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
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'HTTP ' + res.status); });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    btnConfirm.disabled = false;
                    btnConfirm.textContent = 'Simpan';
                    btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
                    Swal.fire({
                        title: 'Oops...',
                        html: data.message || 'Gagal menyimpan foto',
                        icon: 'warning',
                        confirmButtonColor: '#7a5234',
                        confirmButtonText: 'Mengerti'
                    });
                    previewSection.classList.add('hidden');
                    btnCapture.classList.remove('hidden');
                }
            })
            .catch(err => {
                btnConfirm.disabled = false;
                btnConfirm.textContent = 'Simpan';
                btnConfirm.classList.remove('opacity-50', 'cursor-not-allowed');
                Swal.fire({
                    title: 'Error',
                    html: 'Terjadi kesalahan: ' + err.message + '. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonColor: '#7a5234',
                    confirmButtonText: 'Tutup'
                });
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