@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Unit Perusahaan')

<x-admin.page-body>

    <x-admin.card>

        <div class="p-3">

            {{-- ================================================== --}}
            {{-- Button Tambah --}}
            {{-- ================================================== --}}
            @can('unit-create')
                <div class="mb-2">
                    <x-admin.button variant="primary" icon="building-2" href="#" id="btnTambahUnitperusahaan">Tambah Data Unit Perusahaan</x-admin.button>
                </div>
            @endcan

            {{-- ================================================== --}}
            {{-- Table --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Perusahaan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Radius</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider" width="120">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($unitperusahaan as $u)
                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-1.5 text-xs">{{ $loop->iteration }}</td>
                                <td class="px-2 py-1.5 text-xs">{{ $u->unit }}</td>
                                <td class="px-2 py-1.5 text-xs">{{ $u->perusahaan }}</td>
                                <td class="px-2 py-1.5 text-xs">
                                    {{ $u->jam_masuk ? date('H:i', strtotime($u->jam_masuk)) : '�?"' }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    @if ($u->lokasis->isEmpty())
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700">Belum di-set</span>
                                    @else
                                        {{ $u->lokasis->count() }} titik
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs">{{ $u->radius_meter }} m</td>
                                <td class="px-2 py-1.5 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @can('unit-edit')
                                            <x-admin.button variant="edit" icon="square-pen" size="sm" href="#" class="edit" data-id="{{ $u->id }}" />
                                        @endcan
                                        @can('unit-delete')
                                            <form action="/unitperusahaan/{{ $u->id }}/delete" method="POST" class="inline">
                                                @csrf
                                                <x-admin.button variant="danger" icon="trash-2" size="sm" type="submit" class="delete-confirm" />
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </x-admin.card>

</x-admin.page-body>

{{-- ================================================== --}}
{{-- Template Form --}}
{{-- ================================================== --}}
<template id="formTemplate">
    @include('admin.unitperusahaan._form', ['data' => null])
</template>

<x-admin.modal id="modal-unitperusahaanform" title="Form Unit Perusahaan">
    <div id="formContainer"></div>
</x-admin.modal>

@endsection

@php
    $dataJson = $unitperusahaan->map(fn($u) => [
        'id' => $u->id,
        'unit' => $u->unit,
        'perusahaan' => $u->perusahaan,
        'jam_masuk' => $u->jam_masuk,
        'radius_meter' => $u->radius_meter,
        'lokasi' => $u->lokasis->map(fn ($l) => [
            'nama' => $l->nama_lokasi,
            'lat' => $l->lat,
            'lng' => $l->lng,
        ])->values()->all(),
    ])->toJson();
@endphp

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function() {

    var dataJson = {!! $dataJson !!};
    var formTpl = document.getElementById('formTemplate');
    var formContainer = document.getElementById('formContainer');

    function openModal() {
        window.dispatchEvent(new CustomEvent('open-modal-modal-unitperusahaanform'));
    }

    // =====================================================
    // Baris Lokasi (multi titik)
    // =====================================================
    function tambahBarisLokasi(data) {
        var tpl = document.getElementById('tpl-lokasi-row');
        if (!tpl) return;

        var row = tpl.content.firstElementChild.cloneNode(true);

        if (data) {
            row.querySelector('.lok-nama').value = data.nama || '';
            row.querySelector('.lok-lat').value = (data.lat === null || data.lat === undefined) ? '' : data.lat;
            row.querySelector('.lok-lng').value = (data.lng === null || data.lng === undefined) ? '' : data.lng;
        }

        document.getElementById('lokasi-rows').appendChild(row);
        reindexLokasi();
        if (window.lucide) lucide.createIcons();
    }

    function reindexLokasi() {
        document.querySelectorAll('#lokasi-rows .lokasi-row').forEach(function (row, i) {
            row.querySelector('.lok-nama').name = 'lokasis[' + i + '][nama]';
            row.querySelector('.lok-lat').name = 'lokasis[' + i + '][lat]';
            row.querySelector('.lok-lng').name = 'lokasis[' + i + '][lng]';
        });
    }

    // =====================================================
    // Tambah — clone form kosong
    // =====================================================
    document.getElementById('btnTambahUnitperusahaan').addEventListener('click', function() {
        formContainer.innerHTML = '';
        formContainer.appendChild(formTpl.content.cloneNode(true));
        reindexLokasi();
        if (window.lucide) lucide.createIcons();
        openModal();
    });

    // =====================================================
    // Edit — clone + populate dari JSON
    // =====================================================
    document.querySelector('tbody').addEventListener('click', function(e) {
        var editBtn = e.target.closest('.edit');
        if (!editBtn) return;
        e.preventDefault();

        var id = editBtn.getAttribute('data-id');
        var d = dataJson.find(function(item) { return item.id == id; });
        if (!d) return;

        var clone = formTpl.content.cloneNode(true);
        var form = clone.querySelector('form');

        form.action = '/unitperusahaan/' + d.id + '/update';
        form.querySelector('[name="unit"]').value = d.unit;
        form.querySelector('[name="perusahaan"]').value = d.perusahaan;
        form.querySelector('[name="jam_masuk"]').value = d.jam_masuk || '';
        form.querySelector('[name="radius_meter"]').value = d.radius_meter || 100;

        var btn = form.querySelector('button[type="submit"]');
        btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i> Perbarui Data';

        formContainer.innerHTML = '';
        formContainer.appendChild(clone);

        (d.lokasi || []).forEach(function (l) {
            tambahBarisLokasi(l);
        });

        if (window.lucide) lucide.createIcons();

        openModal();
    });

    // =====================================================
    // Tambah / Hapus baris lokasi (delegation)
    // =====================================================
    formContainer.addEventListener('click', function(e) {
        var tambahBtn = e.target.closest('#btnTambahLokasi');
        if (tambahBtn) {
            e.preventDefault();
            tambahBarisLokasi();
            return;
        }

        var hapusBtn = e.target.closest('.hapus-lokasi');
        if (hapusBtn) {
            e.preventDefault();
            hapusBtn.closest('.lokasi-row').remove();
            reindexLokasi();
        }
    });

    // =====================================================
    // Delete Unit Perusahaan
    // =====================================================
    document.addEventListener('click', function(e) {
        var deleteBtn = e.target.closest('.delete-confirm');
        if (!deleteBtn) return;
        e.preventDefault();

        var form = deleteBtn.closest('form');

        Swal.fire({
            title: "Yakin data ini akan dihapus?",
            text: "Data yang sudah dihapus tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus Data",
            cancelButtonText: "Batal",
            backdrop: false
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // =====================================================
    // Validasi Form Submit (delegation)
    // =====================================================
    formContainer.addEventListener('submit', function(e) {
        var form = e.target;
        var unit = form.querySelector('[name="unit"]').value;
        var perusahaan = form.querySelector('[name="perusahaan"]').value;
        var jamMasuk = form.querySelector('[name="jam_masuk"]').value;
        var radius = form.querySelector('[name="radius_meter"]').value;

        if (!unit) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Nama unit tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="unit"]').focus();
            return;
        }
        if (!perusahaan) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Nama perusahaan tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="perusahaan"]').focus();
            return;
        }
        if (!jamMasuk) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Jam masuk harus diisi.", backdrop: false });
            form.querySelector('[name="jam_masuk"]').focus();
            return;
        }
        if (!radius || isNaN(radius) || Number(radius) < 10 || Number(radius) > 10000) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Radius presensi harus antara 10 sampai 10000 meter.", backdrop: false });
            form.querySelector('[name="radius_meter"]').focus();
            return;
        }

        var barisLokasi = form.querySelectorAll('#lokasi-rows .lokasi-row');
        for (var i = 0; i < barisLokasi.length; i++) {
            var nama = barisLokasi[i].querySelector('.lok-nama').value.trim();
            var lat = barisLokasi[i].querySelector('.lok-lat').value.trim();
            var lng = barisLokasi[i].querySelector('.lok-lng').value.trim();

            if (!nama || !lat || !lng || isNaN(lat) || isNaN(lng)
                || Number(lat) < -90 || Number(lat) > 90
                || Number(lng) < -180 || Number(lng) > 180) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Lokasi ke-" + (i + 1) + " belum lengkap atau koordinat tidak valid.",
                    backdrop: false
                });
                return;
            }
        }
    });

    if (window.lucide) lucide.createIcons();

});
</script>
@endpush
