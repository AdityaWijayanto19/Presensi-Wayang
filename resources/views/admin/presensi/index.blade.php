@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Monitoring Presensi')

<x-admin.page-body>

    <x-admin.card>

        <div class="p-3">

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <x-admin.input type="text" name="tanggal" id="tanggal" placeholder="Pilih Tanggal Presensi"
                autocomplete="off" value="{{ date('Y-m-d') }}" icon="calendar" />

            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-12 md:col-span-4">
                    <x-admin.input name="nama_karyawan" id="nama_karyawan" placeholder="Cari Nama Karyawan"
                        autocomplete="off" />
                </div>
                <div class="col-span-12 md:col-span-3">
                    <x-admin.select name="unit" id="unit" searchable placeholder="Semua Unit">
                        @foreach ($unitperusahaan as $u)
                            <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>
                                {{ $u->unit }}</option>
                        @endforeach
                    </x-admin.select>
                </div>
                <div class="col-span-12 md:col-span-3">
                    <x-admin.select name="filter_ketepatan" id="filter_ketepatan" placeholder="Semua Ketepatan">
                        <option value="-1">Semua Ketepatan</option>
                        <option value="0">Tepat Waktu</option>
                        <option value="1">Terlambat</option>
                    </x-admin.select>
                </div>
                <div class="col-span-12 md:col-span-2">
                    <x-admin.button variant="primary" icon="search" id="btnCari" block>Cari Data</x-admin.button>
                </div>
            </div>

            {{-- ================================================== --}}
            {{-- Tabel Monitoring --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto mt-2">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                No.</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                NIK</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Nama Karyawan</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Unit Perusahaan</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Masuk</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Foto Masuk</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Pulang</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Foto Pulang</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Keterangan Presensi</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Lokasi</th>
                        </tr>
                    </thead>
                    <tbody id="loadpresensi" class="divide-y divide-slate-200">
                    </tbody>
                </table>
            </div>

        </div>

    </x-admin.card>

</x-admin.page-body>

{{-- ================================================== --}}
{{-- Modal Peta --}}
{{-- ================================================== --}}
<x-admin.modal id="modal-tampilkanpeta" title="Lokasi Presensi Karyawan">
    <div id="loadmap">
        {{-- Map akan dimuat menggunakan AJAX --}}
    </div>
</x-admin.modal>

{{-- ================================================== --}}
{{-- Modal Edit Presensi --}}
{{-- ================================================== --}}
<x-admin.modal id="modal-editpresensi" title="Edit Data Presensi">
    <form id="formEditPresensi" method="POST">
        @csrf
        <input type="hidden" name="presensi_id" id="edit_presensi_id">

        <x-admin.input type="time" name="jam_in" id="edit_jam_in"
            label="Jam Masuk <span class='text-red-500'>*</span>" step="1" required />

        <x-admin.input type="time" name="jam_out" id="edit_jam_out" label="Jam Pulang" step="1" />
        <small class="text-slate-500 text-xs">Kosongkan jika belum presensi pulang</small>

        <div class="mt-2">
            <x-admin.button variant="primary" icon="save" type="submit" block>Simpan Perubahan</x-admin.button>
        </div>
    </form>
</x-admin.modal>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function() {

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
            var filterKetepatan = document.getElementById('filter_ketepatan').value;

            fetch('/getpresensi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({
                        tanggal: tanggal,
                        nama_karyawan: nama_karyawan,
                        unit: unit,
                        filter_ketepatan: filterKetepatan
                    })
                })
                .then(function(r) {
                    return r.text();
                })
                .then(function(html) {
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
        document.addEventListener('click', function(e) {
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
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.edit-presensi');
            if (btn) {
                e.preventDefault();
                var id = btn.dataset.id;
                var jamIn = btn.dataset.jam_in;
                var jamOut = btn.dataset.jam_out;

                document.getElementById('edit_presensi_id').value = id;
                document.getElementById('edit_jam_in').value = jamIn || '';
                document.getElementById('edit_jam_out').value = jamOut || '';
                document.getElementById('formEditPresensi').setAttribute('action', '/presensi/' + id +
                    '/update');
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

            setTimeout(function() {
                map.invalidateSize();
                marker.openPopup();
            }, 300);
        }

        // ==================================================
        // Tampilkan Peta Masuk (event delegation)
        // ==================================================
        document.addEventListener('click', function(e) {
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
                    body: new URLSearchParams({
                        id: id
                    })
                }).then(function(r) {
                    return r.text();
                }).then(function(html) {
                    if (mapContainer) mapContainer.innerHTML = html;
                    window.dispatchEvent(new CustomEvent('open-modal-modal-tampilkanpeta'));
                    setTimeout(initMapFromContainer, 150);
                });
            }
        });

        // ==================================================
        // Tampilkan Peta Pulang (event delegation)
        // ==================================================
        document.addEventListener('click', function(e) {
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
                    body: new URLSearchParams({
                        id: id
                    })
                }).then(function(r) {
                    return r.text();
                }).then(function(html) {
                    if (mapContainer) mapContainer.innerHTML = html;
                    window.dispatchEvent(new CustomEvent('open-modal-modal-tampilkanpeta'));
                    setTimeout(initMapFromContainer, 150);
                });
            }
        });

        if (window.lucide) lucide.createIcons();

    });
</script>
@endpush
