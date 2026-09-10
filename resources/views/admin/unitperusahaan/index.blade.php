@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Unit Perusahaan')

<x-admin.page-body>

    <x-admin.card>

        <div class="p-3">

            {{-- ================================================== --}}
            {{-- Alert --}}
            {{-- ================================================== --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-md text-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (Session::get('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-md text-sm">
                    {{ Session::get('error') }}
                </div>
            @endif

            @if (Session::get('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-md text-sm">
                    {{ Session::get('success') }}
                </div>
            @endif

            {{-- ================================================== --}}
            {{-- Button Tambah --}}
            {{-- ================================================== --}}
            @can('unit-create')
                <div class="mb-2">
                    <a href="#"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                        id="btnTambahUnitperusahaan">
                        <i data-lucide="building-2" style="width:18px;height:18px;"></i>
                        Tambah Data Unit Perusahaan
                    </a>
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
                                    {{ $u->jam_masuk ? date('H:i', strtotime($u->jam_masuk)) : '—' }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @can('unit-edit')
                                            <a href="#"
                                                class="edit bg-cyan-500 text-white px-1.5 py-1.5 rounded hover:bg-cyan-600 transition-colors text-[10px] font-medium inline-flex items-center"
                                                data-id="{{ $u->id }}">
                                                <i data-lucide="square-pen" style="width:12px;height:12px;"></i>
                                            </a>
                                        @endcan
                                        @can('unit-delete')
                                            <form action="/unitperusahaan/{{ $u->id }}/delete" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="delete-confirm bg-red-600 text-white px-1.5 py-1.5 rounded hover:bg-red-700 transition-colors text-[10px] font-medium inline-flex items-center">
                                                    <i data-lucide="trash-2" style="width:12px;height:12px;"></i>
                                                </button>
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
    // Tambah — clone form kosong
    // =====================================================
    document.getElementById('btnTambahUnitperusahaan').addEventListener('click', function() {
        formContainer.innerHTML = '';
        formContainer.appendChild(formTpl.content.cloneNode(true));
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

        var btn = form.querySelector('button[type="submit"]');
        btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i> Perbarui Data';

        formContainer.innerHTML = '';
        formContainer.appendChild(clone);

        if (window.lucide) lucide.createIcons();

        openModal();
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
    });

    if (window.lucide) lucide.createIcons();

});
</script>
@endpush
