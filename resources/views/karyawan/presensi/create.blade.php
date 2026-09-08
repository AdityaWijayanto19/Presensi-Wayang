@extends('layouts.presensi')

@section('header')

    <div class="appHeader bg-coklat text-light">
        <div class="pageTitle">WAG - Presensi Digital</div>
        <div class="right"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@endsection


@section('content')

    <input type="hidden" id="lokasi">

    {{-- Mobile: vertikal | Desktop: dua kolom --}}
    <div class="mt-[70px] px-2 flex flex-col md:flex-row md:gap-6 md:items-start">

        {{-- Kiri: Webcam --}}
        <div class="w-full md:w-1/2 flex justify-center">
            <div class="webcam-capture md:max-w-full"></div>
        </div>

        {{-- Kanan: Button + Map --}}
        <div class="w-full md:w-1/2 flex flex-col gap-3 mt-3 md:mt-0">
            @if ($cek > 0)
                <button id="takeabsen" class="btn btn-danger btn-lg w-full">
                    <i data-lucide="camera"></i>
                    Presensi Pulang
                </button>
            @else
                <button id="takeabsen" class="btn btn-primary btn-lg w-full">
                    <i data-lucide="camera"></i>
                    Presensi Masuk
                </button>
            @endif

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

<script>

    document.addEventListener("DOMContentLoaded", function () {

        var notifikasi_in = document.getElementById("notifikasi_in");
        var notifikasi_out = document.getElementById("notifikasi_out");

        var lokasi = document.getElementById('lokasi');

        function cekLokasi() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    setTimeout(function () {
                        successCallback(position);
                    }, 500);
                }, errorCallback);
            }
        }

        function errorCallback() {
            Swal.fire({
                title: 'Lokasi Belum Aktif',
                text: 'Silakan aktifkan GPS/Lokasi terlebih dahulu untuk melakukan presensi.',
                icon: 'warning',
                confirmButtonText: 'Cek Ulang Lokasi',
                confirmButtonColor: '#9c6b43'
            }).then((result) => {
                if (result.isConfirmed) {
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
                } else {
                    var camEl = document.querySelector('.webcam-capture');
                    if (camEl) camEl.innerHTML = '<div class="p-4 text-center text-[12px] text-[#a8a29e]">Izin kamera belum diaktifkan. <a href="/settings" class="text-sky-700 underline">Aktifkan di Pengaturan</a></div>';
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
                cekLokasi();
            });

        function successCallback(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            lokasi.value = lat + "," + lng;

            if (document.getElementById('map')) {
                var map = L.map('map').setView([lat, lng], 17);

                L.tileLayer('http://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 19,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }).addTo(map);

                var tileLoaded = 0;
                map.on('tileload', function () {
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

                L.marker([lat, lng]).addTo(map).bindPopup("Lokasimu saat ini").openPopup();

                L.circle([lat, lng], {
                    color: '#47E016',
                    fillColor: '#47E016',
                    fillOpacity: 0.5,
                    radius: 15
                }).addTo(map);
            }
        }

        function errorCallback() {
            Swal.fire({
                title: 'Lokasi Belum Aktif',
                text: 'Silakan aktifkan GPS/Lokasi terlebih dahulu untuk melakukan presensi.',
                icon: 'warning',
                confirmButtonText: 'Cek Ulang Lokasi',
                confirmButtonColor: '#9c6b43'
            }).then((result) => {
                if (result.isConfirmed) {
                    cekLokasi();
                }
            });
        }

        $("#takeabsen").click(function (e) {
            var lokasi = $("#lokasi").val();

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

            Webcam.snap(function (uri) {
                var image = uri;

                $.ajax({
                    type: 'POST',
                    url: '/presensi/store',
                    data: {
                        _token: "{{ csrf_token() }}",
                        image: image,
                        lokasi: lokasi
                    },
                    cache: false,
                    success: function (respond) {
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
                                Webcam.reset();
                                location.href = '/dashboard';
                            }, 3100);

                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: status[1],
                                icon: 'error',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#9c6b43'
                            });
                        }
                    }
                });
            });
        });

    });

</script>

@endpush
