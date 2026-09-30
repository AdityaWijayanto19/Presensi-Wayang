@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">WAG - Presensi Digital</div>
        <div class="right"></div>
    </div>

@endsection


@section('content')

    <input type="hidden" id="lokasi">

    {{-- Mobile: vertikal | Desktop: dua kolom --}}
    <div class="mt-[70px] px-2 flex flex-col md:flex-row md:gap-6 md:items-start" x-data="presensiFaceCheck()">

        {{-- Kiri: Webcam --}}
        <div class="w-full md:w-1/2 flex flex-col items-center">
            <div class="relative mx-auto w-[360px] max-w-full"
                 :class="status !== 'error' ? 'min-h-[480px]' : ''">
                <div class="webcam-capture md:max-w-full"></div>
                <div id="face-feedback"
                    class="rounded-[15px] px-3 py-2 text-xs font-medium text-center"
                    :class="badgeClass" x-text="text">Menyiapkan kamera…</div>
            </div>
        </div>

        {{-- Kanan: Button + Map --}}
        <div class="w-full md:w-1/2 flex flex-col gap-3 mt-3 md:mt-0">
            @if ($cek > 0)
                <button id="takeabsen" class="btn btn-danger btn-lg w-full disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!canSubmit">
                    <i data-lucide="camera"></i>
                    Presensi Pulang
                </button>
            @else
                <button id="takeabsen" class="btn btn-primary btn-lg w-full disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!canSubmit">
                    <i data-lucide="camera"></i>
                    Presensi Masuk
                </button>
            @endif

            <div id="status-radius" class="hidden"></div>

            <div id="map">
                <div id="map-loader" class="flex items-center justify-center h-full bg-gray-100 rounded-[15px]">
                    <div class="text-center">
                        <div class="inline-block w-8 h-8 rounded-full animate-spin mb-2" style="border: 3px solid #e5e7eb; border-top-color: #7a5234;"></div>
                        <p class="text-gray-400 text-sm">Memuat peta...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Audio Notifikasi --}}
    <audio id="notifikasi_in" style="display:none;">
        <source src="{{ asset('assets/audio/presensimasuk_berhasil.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_out" style="display:none;">
        <source src="{{ asset('assets/audio/presensipulang_berhasil.mp3') }}" type="audio/mpeg">
    </audio>

@endsection


@push('myscript')

<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>

    function presensiFaceCheck() {
        return {
            status: 'checking',
            text: 'Menyiapkan kamera…',
            get canSubmit() {
                return this.status === 'ok' || this.status === 'unavailable';
            },
            get badgeClass() {
                var pos = this.status === 'error'
                    ? 'static mt-3'
                    : 'absolute top-2 left-2 right-2 z-50';
                if (this.status === 'ok') return pos + ' bg-green-100 text-green-700';
                if (this.status === 'error') return pos + ' bg-red-100 text-red-700';
                if (this.status === 'unavailable') return pos + ' bg-stone-200 text-stone-700';
                return pos + ' bg-amber-100 text-amber-700';
            },
            set: function (status, text) {
                this.status = status;
                this.text = text;
            }
        };
    }

    var PESAN_FACE = {
        unavailable: 'Pemeriksaan wajah tidak tersedia — silakan lanjutkan presensi',
        kamera_mati: 'Izin kamera belum diaktifkan',
        kamera_tidak_siap: 'Kamera tidak aktif — beri izin kamera lalu muat ulang halaman'
    };

    var faceStatus = 'checking';
    var faceText = 'Menyiapkan kamera…';
    var faceCheckHandle = null;
    var faceCheckStopped = false;
    var faceVideoPoll = null;

    function setFaceStatus(status, text) {
        if (faceCheckStopped) return;

        faceStatus = status;
        faceText = text;

        var alpineEl = document.querySelector('[x-data="presensiFaceCheck()"]');
        if (!alpineEl) return;

        var data = (typeof Alpine !== 'undefined' && Alpine.$data)
            ? Alpine.$data(alpineEl)
            : alpineEl._x_dataStack && alpineEl._x_dataStack[0];

        if (data && typeof data.set === 'function') {
            data.set(status, text);
        }
    }

    function mulaiFaceQualityCheck() {
        var percobaan = 0;

        function pantauVideo() {
            var video = document.querySelector('.webcam-capture video');

            if (video && video.readyState >= 2 && video.videoWidth > 0) {
                clearInterval(faceVideoPoll);
                faceVideoPoll = null;
                mulaiPemeriksaan(video);
                return;
            }

            percobaan++;
            if (percobaan === 40) {
                setFaceStatus('error', PESAN_FACE.kamera_tidak_siap);
            }
        }

        function mulaiPemeriksaan(video) {
            if (faceCheckStopped) return;

            if (typeof window.initFaceQualityCheck !== 'function') {
                setFaceStatus('unavailable', PESAN_FACE.unavailable);
                return;
            }

            window.initFaceQualityCheck({
                video: video,
                onResult: function (hasil) {
                    setFaceStatus(hasil.status, hasil.text);
                }
            }).then(function (handle) {
                faceCheckHandle = handle;
                if (faceCheckStopped && handle && typeof handle.stop === 'function') {
                    handle.stop();
                }
            }).catch(function (error) {
                console.warn('[face-quality] gagal memuat modul:', error);
                setFaceStatus('unavailable', PESAN_FACE.unavailable);
            });
        }

        faceVideoPoll = setInterval(pantauVideo, 500);
        pantauVideo();
    }

    function hentikanFaceCheck() {
        if (faceCheckStopped) return;
        faceCheckStopped = true;

        if (faceVideoPoll !== null) {
            clearInterval(faceVideoPoll);
            faceVideoPoll = null;
        }

        if (faceCheckHandle && typeof faceCheckHandle.stop === 'function') {
            faceCheckHandle.stop();
            faceCheckHandle = null;
        }
    }

    window.addEventListener('pagehide', hentikanFaceCheck);

    document.addEventListener("DOMContentLoaded", function () {

        var notifikasi_in = document.getElementById("notifikasi_in");
        var notifikasi_out = document.getElementById("notifikasi_out");

        var lokasi = document.getElementById('lokasi');

        // Konfigurasi geofencing dari server
        var titikKantor = @json($lokasiKantor);
        var radiusMeter = {{ (int) $radiusMeter }};
        var modeWfh = @json((bool) $modeWfh);
        var BASE_BADGE = 'rounded-[15px] px-3 py-2 text-xs font-medium';

        // --- State akurasi posisi realtime ---
        var OPSESI_GPS = { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 };
        var JENDELA_FIX = 10000;      // window smoothing 10 detik
        var JENDELA_BASI = 5000;      // fix dianggap basi setelah 5 detik
        var BATAS_AKURASI_BURUK = 15; // di atas ini status radius disembunyikan

        var fixBuffer = [];
        var posisiHalus = null;
        var akurasiEfektif = null;
        var watchId = null;
        var watchAktif = false;
        var percobaanUlang = 0;
        var dialogLokasiTampil = false;

        var peta = null;
        var petaSiap = false;
        var markerUser = null;
        var lingkaranAkurasi = null;
        var layerKantor = [];

        function modeKantorAktif() {
            return !modeWfh && titikKantor.length > 0;
        }

        function parseLokasiNilai(nilai) {
            var bagian = (nilai || '').split(',');
            if (bagian.length !== 2) return null;

            var lat = parseFloat(bagian[0]);
            var lng = parseFloat(bagian[1]);

            return (isNaN(lat) || isNaN(lng)) ? null : [lat, lng];
        }

        function jarakMeter(lat1, lng1, lat2, lng2) {
            var R = 6371000;
            var dLat = (lat2 - lat1) * Math.PI / 180;
            var dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
                + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
                * Math.sin(dLng / 2) * Math.sin(dLng / 2);

            return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function titikTerdekat(lat, lng) {
            var hasil = null;

            titikKantor.forEach(function (titik) {
                var jarak = jarakMeter(lat, lng, titik.lat, titik.lng);
                if (hasil === null || jarak < hasil.jarak) {
                    hasil = { jarak: jarak, nama: titik.nama };
                }
            });

            return hasil;
        }

        function tampilkanStatusRadius(lat, lng, akurasi) {
            var el = document.getElementById('status-radius');
            if (!el) return;

            if (modeWfh) {
                el.className = BASE_BADGE + ' bg-blue-100 text-blue-700';
                el.textContent = 'Mode WFH — bebas lokasi';
                return;
            }

            if (titikKantor.length === 0) {
                el.className = BASE_BADGE + ' bg-amber-100 text-amber-700';
                el.textContent = 'Lokasi kantor belum dikonfigurasi';
                return;
            }

            // Akurasi jelek → status radius tidak bisa dipercaya, tampilkan progres sinyal
            if (akurasi != null && akurasi > BATAS_AKURASI_BURUK) {
                el.className = BASE_BADGE + ' bg-amber-100 text-amber-700';
                el.textContent = 'Menangkap sinyal GPS… (±' + Math.round(akurasi) + ' m)';
                return;
            }

            var terdekat = titikTerdekat(lat, lng);
            if (!terdekat) return;

            var infoAkurasi = (akurasi != null) ? ', ±' + Math.round(akurasi) + ' m' : '';

            if (terdekat.jarak <= radiusMeter) {
                el.className = BASE_BADGE + ' bg-green-100 text-green-700';
                el.textContent = 'Dalam radius — ' + terdekat.nama + ' (' + Math.round(terdekat.jarak) + ' m' + infoAkurasi + ')';
            } else {
                el.className = BASE_BADGE + ' bg-red-100 text-red-700';
                el.textContent = 'Di luar radius — ' + Math.round(terdekat.jarak) + ' m dari ' + terdekat.nama + infoAkurasi;
            }
        }

        // ===================================================================
        // Akurasi posisi realtime: watchPosition + smoothing
        // ===================================================================

        function terimaFix(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var sigma = position.coords.accuracy;

            if (typeof sigma !== 'number' || !isFinite(sigma) || sigma <= 0) {
                sigma = 30;
            }

            var sekarang = Date.now();

            // 1. Outlier reject: lompat > 3σ dari estimasi, kecuali buffer sudah basi
            if (posisiHalus && fixBuffer.length > 0) {
                var lompatan = jarakMeter(lat, lng, posisiHalus.lat, posisiHalus.lng);
                var basi = sekarang - fixBuffer[fixBuffer.length - 1].ts > JENDELA_BASI;

                if (lompatan > 3 * Math.max(posisiHalus.sigma, 1) && !basi) {
                    return;
                }

                if (basi) {
                    fixBuffer = [];
                }
            }

            // 2. Masukkan fix ke buffer, buang yang lewat window 10 detik
            fixBuffer.push({ lat: lat, lng: lng, sigma: sigma, ts: sekarang });
            fixBuffer = fixBuffer.filter(function (f) {
                return sekarang - f.ts <= JENDELA_FIX;
            });

            // 3. Inverse-variance weighted average: pos = Σ(pᵢ/σᵢ²) / Σ(1/σᵢ²)
            var totalBobot = 0, latTotal = 0, lngTotal = 0, sigmaMin = null;

            fixBuffer.forEach(function (f) {
                var s = Math.max(f.sigma, 1);
                var bobot = 1 / (s * s);

                totalBobot += bobot;
                latTotal += f.lat * bobot;
                lngTotal += f.lng * bobot;

                if (sigmaMin === null || f.sigma < sigmaMin) sigmaMin = f.sigma;
            });

            if (totalBobot === 0) return;

            posisiHalus = {
                lat: latTotal / totalBobot,
                lng: lngTotal / totalBobot,
                sigma: sigmaMin,
                ts: sekarang
            };

            akurasiEfektif = sigmaMin;
            percobaanUlang = 0;
            lokasi.value = posisiHalus.lat + ',' + posisiHalus.lng;

            if (!petaSiap) {
                inisialisasiPeta(posisiHalus.lat, posisiHalus.lng);
            } else {
                perbaruiPeta(posisiHalus.lat, posisiHalus.lng, sigmaMin);
            }

            tampilkanStatusRadius(posisiHalus.lat, posisiHalus.lng, sigmaMin);
        }

        function inisialisasiPeta(lat, lng) {
            if (petaSiap || !document.getElementById('map')) return;
            petaSiap = true;

            peta = L.map('map').setView([lat, lng], 17);

            L.tileLayer('https://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 19,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(peta);

            var tileLoaded = 0;
            peta.on('tileload', function () {
                tileLoaded++;
                if (tileLoaded >= 3) {
                    var loader = document.getElementById('map-loader');
                    if (loader) loader.remove();
                }
            });
            setTimeout(function () {
                var loader = document.getElementById('map-loader');
                if (loader) loader.remove();
            }, 5000);

            markerUser = L.marker([lat, lng]).addTo(peta).bindPopup('Lokasimu saat ini').openPopup();

            lingkaranAkurasi = L.circle([lat, lng], {
                radius: 0,
                color: '#2563eb',
                weight: 1,
                dashArray: '4 4',
                fillColor: '#3b82f6',
                fillOpacity: 0.10,
                interactive: false
            }).addTo(peta);

            if (!modeWfh) {
                titikKantor.forEach(function (titik) {
                    var label = document.createElement('span');
                    label.textContent = titik.nama;

                    L.marker([titik.lat, titik.lng]).addTo(peta).bindPopup(label);

                    layerKantor.push({
                        titik: titik,
                        lingkaran: L.circle([titik.lat, titik.lng], {
                            color: '#47E016',
                            fillColor: '#47E016',
                            fillOpacity: 0.35,
                            radius: radiusMeter
                        }).addTo(peta)
                    });
                });
            }

            if (!modeWfh && titikKantor.length > 0) {
                var bounds = L.latLngBounds([[lat, lng]]);
                titikKantor.forEach(function (titik) {
                    bounds.extend([titik.lat, titik.lng]);
                });
                peta.fitBounds(bounds.pad(0.35), { maxZoom: 17 });
            }
        }

        function perbaruiPeta(lat, lng, akurasi) {
            if (!petaSiap) return;

            markerUser.setLatLng([lat, lng]);

            if (lingkaranAkurasi) {
                lingkaranAkurasi.setLatLng([lat, lng]);
                lingkaranAkurasi.setRadius(Math.max(akurasi, 1));
            }

            layerKantor.forEach(function (layer) {
                var dalamRadius = jarakMeter(lat, lng, layer.titik.lat, layer.titik.lng) <= radiusMeter;
                var warna = dalamRadius ? '#47E016' : '#E01616';

                layer.lingkaran.setStyle({ color: warna, fillColor: warna });
            });
        }

        function cekLokasi() {
            if (!navigator.geolocation) {
                errorCallback({ code: 0 });
                return;
            }

            // Fix pertama cepat untuk paint awal
            navigator.geolocation.getCurrentPosition(terimaFix, function (err) {
                if (!posisiHalus) errorCallback(err);
            }, OPSESI_GPS);

            pasangWatch();
        }

        function pasangWatch() {
            if (!navigator.geolocation || watchAktif) return;

            watchAktif = true;
            watchId = navigator.geolocation.watchPosition(terimaFix, onWatchError, OPSESI_GPS);
        }

        function onWatchError(err) {
            watchAktif = false;

            // Posisi sudah ada: jangan spam dialog, coba pasang ulang pelan-pelan
            if (posisiHalus) {
                if (percobaanUlang < 3) {
                    percobaanUlang++;
                    setTimeout(pasangWatch, 3000);
                }
                return;
            }

            errorCallback(err);
        }

        function hentikanPantauan() {
            if (watchId !== null && navigator.geolocation) {
                navigator.geolocation.clearWatch(watchId);
            }
            watchId = null;
            watchAktif = false;
        }

        window.addEventListener('pagehide', hentikanPantauan);

        function errorCallback() {
            if (dialogLokasiTampil) return;
            dialogLokasiTampil = true;

            Swal.fire({
                title: 'Lokasi Belum Aktif',
                text: 'Silakan aktifkan GPS/Lokasi terlebih dahulu untuk melakukan presensi.',
                icon: 'warning',
                confirmButtonText: 'Cek Ulang Lokasi',
                confirmButtonColor: '#9c6b43'
            }).then((result) => {
                dialogLokasiTampil = false;
                if (result.isConfirmed) {
                    percobaanUlang = 0;
                    cekLokasi();
                }
            });
        }

        // Cek permission preferences sebelum request kamera & lokasi
        fetch('/api/user/permissions', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(perms => {
                if (perms.camera) {
                    Webcam.set({
                        height: 480,
                        width: 360,
                        image_format: 'jpeg',
                        quality: 95
                    });
                    Webcam.attach('.webcam-capture');
                    mulaiFaceQualityCheck();
                } else {
                    var camEl = document.querySelector('.webcam-capture');
                    if (camEl) camEl.innerHTML = '<div class="p-4 text-center text-[12px] text-[#a8a29e]">Izin kamera belum diaktifkan. <a href="/settings" class="text-sky-700 underline">Aktifkan di Pengaturan</a></div>';
                    setFaceStatus('error', PESAN_FACE.kamera_mati);
                }
                if (perms.location) {
                    cekLokasi();
                } else {
                    var mapEl = document.getElementById('map');
                    if (mapEl) mapEl.innerHTML = '<div class="p-4 text-center text-[12px] text-[#a8a29e]">Izin lokasi belum diaktifkan. <a href="/settings" class="text-sky-700 underline">Aktifkan di Pengaturan</a></div>';
                }
            })
            .catch(() => {
                // Fallback: tetap jalan seperti biasa kalau fetch gagal
                Webcam.set({
                    height: 480,
                    width: 360,
                    image_format: 'jpeg',
                    quality: 95
                });
                Webcam.attach('.webcam-capture');
                mulaiFaceQualityCheck();
                cekLokasi();
            });

        document.getElementById('takeabsen').addEventListener('click', function (e) {
            if (faceStatus !== 'ok' && faceStatus !== 'unavailable') {
                Swal.fire({
                    title: 'Wajah Belum Siap',
                    text: faceText || 'Pastikan wajah terlihat jelas di kamera terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#9c6b43'
                });
                return false;
            }

            var lokasi = document.getElementById('lokasi').value;

            if (lokasi == "") {
                Swal.fire({
                    title: 'Lokasi Belum Ditemukan',
                    text: 'Aktifkan GPS dan tunggu lokasi terdeteksi terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#9c6b43'
                });
                return false;
            }

            if (modeKantorAktif()) {
                var posisi = parseLokasiNilai(lokasi);
                var terdekat = posisi ? titikTerdekat(posisi[0], posisi[1]) : null;

                if (terdekat && terdekat.jarak > radiusMeter) {
                    var rincian = 'Anda berada ' + Math.round(terdekat.jarak) + ' m dari lokasi kantor terdekat (radius '
                        + radiusMeter + ' m)';
                    if (akurasiEfektif != null) {
                        rincian += ' dengan akurasi GPS ±' + Math.round(akurasiEfektif) + ' m';
                    }
                    rincian += '. Silakan presensi dari area kantor.';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Di Luar Radius',
                        text: rincian,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#9c6b43'
                    });
                    return false;
                }
            }

            Webcam.snap(function (uri) {
                var image = uri;

                fetch('/presensi/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: new URLSearchParams({
                        _token: "{{ csrf_token() }}",
                        image: image,
                        lokasi: lokasi
                    }),
                    cache: 'no-store'
                })
                .then(function (response) { return response.text(); })
                .then(function (respond) {
                    var status = respond.split("|");

                    if (status[0] == "success") {
                        if (status[2] == "in") {
                            notifikasi_in.play();
                        } else {
                            notifikasi_out.play();
                        }

                        Swal.fire({
                            title: 'Berhasil!',
                            text: status[1],
                            icon: 'success',
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#9c6b43'
                        });

                        setTimeout(function () {
                            hentikanPantauan();
                            hentikanFaceCheck();
                            Webcam.reset();
                            location.href = '/dashboard';
                        }, 3100);

                    } else {
                        Swal.fire({
                            title: 'Presensi Ditolak',
                            text: status[1],
                            icon: 'error',
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#9c6b43'
                        });
                    }
                });
            });
        });

    });

</script>

@endpush
