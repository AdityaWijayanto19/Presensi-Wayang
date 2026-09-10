@extends('layouts.admin.app')

@section('content')

    @section('page_title', 'Monitoring Presensi')

    <x-app.page-body>

        <div class="bg-white rounded-md shadow-sm border border-slate-200 p-4">

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <div>

                {{-- ========================= --}}
                {{-- Tanggal --}}
                {{-- ========================= --}}
                <div class="relative mb-2">

                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">

                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                            <path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M15 3v4" />
                            <path d="M7 3v4" />
                            <path d="M3 11h16" />
                            <path d="M18 16.496v1.504l1 1" />

                        </svg>

                    </span>

                    <input type="text"
                        class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 pl-8 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        id="tanggal"
                        name="tanggal"
                        placeholder="Pilih Tanggal Presensi"
                        autocomplete="off"
                        value="{{ date('Y-m-d') }}">

                </div>

                {{-- ========================= --}}
                {{-- Filter Pencarian --}}
                {{-- ========================= --}}
                <div class="grid grid-cols-12 gap-2 mb-2">

                    <div class="col-span-12 md:col-span-5">

                        <input type="text"
                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="nama_karyawan"
                            placeholder="Cari Nama Karyawan"
                            autocomplete="off">

                    </div>

                    <div class="col-span-12 md:col-span-5">

                        <select class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="unit">

                            <option value="">
                                Semua Unit
                            </option>

                            @foreach ($unitperusahaan as $u)

                                <option value="{{ $u->unit }}">
                                    {{ $u->unit }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-span-12 md:col-span-2">

                        <button type="button"
                            class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                            id="btnCari">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round">

                                <circle cx="10.5" cy="10.5" r="7.5" />
                                <line x1="21" y1="21" x2="15.8" y2="15.8" />

                            </svg>

                            Cari Data

                        </button>

                    </div>

                </div>

            </div>

            {{-- ================================================== --}}
            {{-- Tabel Monitoring --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead>

                        <tr>

                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No.</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Masuk</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Foto Masuk</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Pulang</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Foto Pulang</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Keterangan Presensi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Lembur</th>

                        </tr>

                    </thead>

                    <tbody id="loadpresensi" class="divide-y divide-slate-200">

                    </tbody>

                </table>

            </div>

        </div>

    </x-app.page-body>

    {{-- ================================================== --}}
    {{-- Modal Peta --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-tampilkanpeta" title="Lokasi Presensi Karyawan">
        <div id="loadmap">

            {{-- Map akan dimuat menggunakan AJAX --}}

        </div>
    </x-app.modal>

    {{-- ================================================== --}}
    {{-- Modal Edit Presensi --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-editpresensi" title="Edit Data Presensi">
        <form id="formEditPresensi" method="POST">
            @csrf
            <input type="hidden" name="presensi_id" id="edit_presensi_id">

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Jam Masuk</label>
                <input type="time" name="jam_in" id="edit_jam_in" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" step="1" required>
            </div>

            <div class="mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1 font-bold">Jam Pulang</label>
                <input type="time" name="jam_out" id="edit_jam_out" class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" step="1">
                <small class="text-slate-500 text-sm">Kosongkan jika belum presensi pulang</small>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">Simpan Perubahan</button>
        </form>
    </x-app.modal>

@endsection

@push('myscript')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        flatpickr("#tanggal", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            allowInput: true,
            disableMobile: "true"
        });

        // ==================================================
        // Load Data Presensi
        // ==================================================
        function loadpresensi() {

            var tanggal = document.getElementById('tanggal').value;
            var nama_karyawan = document.getElementById('nama_karyawan').value;
            var unit = document.getElementById('unit').value;

            fetch('/getpresensi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new URLSearchParams({
                    tanggal: tanggal,
                    nama_karyawan: nama_karyawan,
                    unit: unit
                })
            })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                document.getElementById('loadpresensi').innerHTML = html;
            });

        }

        // ==================================================
        // Filter Berdasarkan Tanggal
        // ==================================================
        document.getElementById('tanggal').addEventListener('change', loadpresensi);

        // ==================================================
        // Button Cari
        // ==================================================
        document.getElementById('btnCari').addEventListener('click', loadpresensi);

        // ==================================================
        // Load Pertama Kali
        // ==================================================
        loadpresensi();

        // ==================================================
        // Preview Foto Presensi
        // ==================================================
        document.addEventListener('click', function (e) {
            var el = e.target.closest('.foto-monitoring');
            if (el) {
                Swal.fire({
                    imageUrl: el.getAttribute('src'),
                    imageAlt: 'Foto Presensi',
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '480px',
                    backdrop: false
                });
            }
        });

        // ==================================================
        // Edit Presensi Modal
        // ==================================================
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.edit-presensi');
            if (btn) {
                e.preventDefault();
                var id = btn.dataset.id;
                var jamIn = btn.dataset.jam_in;
                var jamOut = btn.dataset.jam_out;

                document.getElementById('edit_presensi_id').value = id;
                document.getElementById('edit_jam_in').value = jamIn || '';
                document.getElementById('edit_jam_out').value = jamOut || '';
                document.getElementById('formEditPresensi').setAttribute('action', '/presensi/' + id + '/update');
                window.dispatchEvent(new CustomEvent('open-modal-modal-editpresensi'));
            }
        });

        // ==================================================
        // Inisialisasi Peta dari data-* attributes
        // ==================================================
        function initMapFromContainer() {
            var mapEl = document.getElementById('map');
            if (!mapEl || !mapEl.dataset.lokasi) return;

            var lokasi = mapEl.dataset.lokasi.split(',');
            var latitude = lokasi[0];
            var longitude = lokasi[1];
            var label = mapEl.dataset.label || '';
            var color = mapEl.dataset.color || 'red';
            var fillColor = color === 'blue' ? '#0d6efd' : '#f03';

            var map = L.map('map').setView([latitude, longitude], 16);

            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);

            var marker = L.marker([latitude, longitude]).addTo(map);
            marker.bindPopup(label);

            L.circle([latitude, longitude], {
                color: color,
                fillColor: fillColor,
                fillOpacity: 0.5,
                radius: 15
            }).addTo(map);

            setTimeout(function () {
                map.invalidateSize();
                marker.openPopup();
            }, 300);
        }

        // ==================================================
        // Tampilkan Peta Masuk (event delegation)
        // ==================================================
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.tampilkanpetamasuk');
            if (btn) {
                e.preventDefault();
                var id = btn.getAttribute('data-id');
                var mapContainer = document.getElementById('loadmap');

                fetch('/tampilkanpetamasuk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({ id: id })
                }).then(function(r) { return r.text(); }).then(function(html) {
                    if (mapContainer) mapContainer.innerHTML = html;
                    window.dispatchEvent(new CustomEvent('open-modal-modal-tampilkanpeta'));
                    setTimeout(initMapFromContainer, 150);
                });
            }
        });

        // ==================================================
        // Tampilkan Peta Pulang (event delegation)
        // ==================================================
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.tampilkanpetapulang');
            if (btn) {
                e.preventDefault();
                var id = btn.getAttribute('data-id');
                var mapContainer = document.getElementById('loadmap');

                fetch('/tampilkanpetapulang', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({ id: id })
                }).then(function(r) { return r.text(); }).then(function(html) {
                    if (mapContainer) mapContainer.innerHTML = html;
                    window.dispatchEvent(new CustomEvent('open-modal-modal-tampilkanpeta'));
                    setTimeout(initMapFromContainer, 150);
                });
            }
        });

    });

</script>

@endpush