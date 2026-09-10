@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Karyawan')

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
            @can('karyawan-create')
                <div class="mb-2">
                    <a href="#"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                        id="btnTambahkaryawan">
                        <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
                        Tambah Data Karyawan
                    </a>
                </div>
            @endcan

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <form action="/panel/karyawan" method="GET">
                <div class="grid grid-cols-12 gap-2 mb-2">
                    <div class="col-span-12 md:col-span-4">
                        <x-admin.input
                            name="nama_karyawan"
                            id="nama_karyawan"
                            placeholder="Cari Karyawan"
                            value="{{ Request('nama_karyawan') }}"
                            autocomplete="off"
                        />
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.select name="jabatan_filter" placeholder="Semua Jabatan">
                            <option value="Intern" {{ Request('jabatan_filter')=='Intern'?'selected':'' }}>Intern</option>
                            <option value="Staff" {{ Request('jabatan_filter')=='Staff'?'selected':'' }}>Staff</option>
                            <option value="SPV" {{ Request('jabatan_filter')=='SPV'?'selected':'' }}>SPV (Supervisor)</option>
                            <option value="Manager" {{ Request('jabatan_filter')=='Manager'?'selected':'' }}>Manager</option>
                            <option value="GM" {{ Request('jabatan_filter')=='GM'?'selected':'' }}>GM</option>
                            <option value="Direktur" {{ Request('jabatan_filter')=='Direktur'?'selected':'' }}>Direktur</option>
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.select name="unit" placeholder="Semua Unit">
                            @foreach ($unitperusahaan as $u)
                                <option value="{{ $u->unit }}" {{ Request('unit')==$u->unit?'selected':'' }}>{{ $u->unit }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">
                            <i data-lucide="search" style="width:16px;height:16px;"></i>
                            Cari Data
                        </button>
                    </div>
                </div>
            </form>

            {{-- ================================================== --}}
            {{-- Table --}}
            {{-- ================================================== --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead>
                        <tr>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIK</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Posisi</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Atasan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No. HP</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Foto</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider" width="170">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($karyawan as $k)
                            @php
                                $path = asset('storage/uploads/karyawan/' . $k->foto);
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td class="px-2 py-1.5 text-xs">
                                    {{ $loop->iteration + $karyawan->firstItem() - 1 }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    {{ $k->nik }}
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->nama_lengkap }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    @if($k->jabatan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $k->jabatan=='Direktur' ? 'bg-red-100 text-red-700' : ($k->jabatan=='GM' ? 'bg-yellow-100 text-yellow-700' : ($k->jabatan=='Manager' ? 'bg-cyan-100 text-cyan-700' : ($k->jabatan=='SPV' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'))) }}">{{ $k->jabatan }}{{ $k->jabatan=='GM' ? ' (General Manager)' : ($k->jabatan=='SPV' ? ' (Supervisor)' : '') }}</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->posisi }}
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    @if($k->jabatan=='Direktur')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Langsung Admin</span><br><small class="text-slate-500">Tidak ada atasan</small>
                                    @else
                                        {{ $k->atasan->nama_lengkap ?? '—' }}
                                        @if(!empty($k->atasan->jabatan))
                                            <br><small class="text-slate-500">{{ $k->atasan->jabatan }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    {{ $k->no_hp }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    @if ($k->foto == 'nophoto.png')
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center foto-karyawan" style="cursor:pointer;">
                                            <i data-lucide="user" style="width:16px;height:16px;"></i>
                                        </div>
                                    @else
                                        <img src="{{ $path }}?v={{ time() }}" class="w-8 h-8 rounded-full foto-karyawan" style="cursor:pointer;" alt="{{ $k->nama_lengkap }}">
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->unitperusahaan->perusahaan ?? '' }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @can('karyawan-edit')
                                            <a href="#"
                                                class="edit bg-cyan-500 text-white px-1.5 py-1.5 rounded hover:bg-cyan-600 transition-colors text-[10px] font-medium inline-flex items-center"
                                                nik="{{ $k->nik }}" page="{{ request()->get('page', 1) }}">
                                                <i data-lucide="square-pen" style="width:12px;height:12px;"></i>
                                            </a>
                                        @endcan
                                        @can('karyawan-delete')
                                            <form action="/karyawan/{{ $k->nik }}/delete" method="POST" class="inline">
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

            <div class="mt-2">
                {{ $karyawan->links() }}
            </div>

        </div>

    </x-admin.card>

</x-admin.page-body>

{{-- ================================================== --}}
{{-- Modal Form Karyawan (Create / Edit) --}}
{{-- ================================================== --}}
<template id="formTemplate">
    @include('admin.karyawan._form', ['unitperusahaan' => $unitperusahaan, 'karyawan' => null])
</template>

<x-admin.modal id="modal-karyawanform" title="Form Karyawan">
    <div id="formContainer"></div>
</x-admin.modal>

@endsection

@php
    $karyawanJson = $karyawan->map(fn($k) => [
        'nik' => $k->nik,
        'nama_lengkap' => $k->nama_lengkap,
        'posisi' => $k->posisi,
        'jabatan' => $k->jabatan,
        'role_approved' => $k->role_approved,
        'atasan_nik' => $k->atasan_nik,
        'unit' => $k->unit,
        'no_hp' => $k->no_hp,
        'foto' => $k->foto,
        'page' => request()->get('page', 1),
    ])->toJson();
@endphp

@push('myscript')
<script>
document.addEventListener('DOMContentLoaded', function() {

    var karyawanData = {!! $karyawanJson !!};
    var formTpl = document.getElementById('formTemplate');
    var formContainer = document.getElementById('formContainer');

    function openModal() {
        window.dispatchEvent(new CustomEvent('open-modal-modal-karyawanform'));
    }

    // =====================================================
    // Toggle Password Visibility
    // =====================================================
    window.togglePassword = function(inputId, btn) {
        var input = document.getElementById(inputId);
        if (!input) return;
        var eyeOpen = btn.querySelector('.eye-open');
        var eyeClosed = btn.querySelector('.eye-closed');
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = '';
        } else {
            input.type = 'password';
            eyeOpen.style.display = '';
            eyeClosed.style.display = 'none';
        }
    };

    // =====================================================
    // Tambah Karyawan — clone form kosong
    // =====================================================
    document.getElementById('btnTambahkaryawan').addEventListener('click', function() {
        formContainer.innerHTML = '';
        formContainer.appendChild(formTpl.content.cloneNode(true));
        openModal();
    });

    // =====================================================
    // Edit Karyawan — clone + populate dari JSON
    // =====================================================
    document.querySelector('tbody').addEventListener('click', function(e) {
        var editBtn = e.target.closest('.edit');
        if (!editBtn) return;
        e.preventDefault();

        var nik = editBtn.getAttribute('nik');
        var k = karyawanData.find(function(item) { return item.nik == nik; });
        if (!k) return;

        var clone = formTpl.content.cloneNode(true);
        var form = clone.querySelector('form');

        form.action = '/karyawan/' + k.nik + '/update';
        var nikInput = form.querySelector('[name="nik"]');
        nikInput.value = k.nik;
        nikInput.readOnly = true;
        nikInput.classList.add('bg-slate-50');
        form.querySelector('[name="nama_lengkap"]').value = k.nama_lengkap;
        form.querySelector('[name="posisi"]').value = k.posisi;
        form.querySelector('[name="jabatan"]').value = k.jabatan || '';
        form.querySelector('[name="unit"]').value = k.unit || '';
        form.querySelector('[name="no_hp"]').value = k.no_hp;
        form.querySelector('[name="password"]').placeholder = 'Kosongkan jika tidak diubah';
        form.querySelector('[name="password"]').removeAttribute('required');

        var fotoLama = form.querySelector('[name="foto_lama"]');
        if (fotoLama) fotoLama.value = k.foto;

        var btn = form.querySelector('button[type="submit"]');
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13.5 16h-9.5a1 1 0 0 1 -1 -1v-10a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v7.5"/><path d="M7 20h5"/><path d="M9 16v4"/><path d="M19 16v6"/><path d="M22 19l-3 3l-3 -3"/></svg> Perbarui Data';

        formContainer.innerHTML = '';
        formContainer.appendChild(clone);

        // Trigger cascading: jabatan change
        var jabatanSelect = form.querySelector('[name="jabatan"]');
        if (jabatanSelect) jabatanSelect.dispatchEvent(new Event('change'));

        // Set role_approved & trigger cascading
        if (k.role_approved) {
            var roleApprovedSelect = form.querySelector('[name="role_approved"]');
            if (roleApprovedSelect) {
                roleApprovedSelect.value = k.role_approved;
                roleApprovedSelect.dispatchEvent(new Event('change'));
                // Fetch atasan then set value
                fetchAtasanForEdit(k.role_approved, k.atasan_nik, k.nik);
            }
        }

        openModal();
    });

    // =====================================================
    // Fetch Atasan untuk Edit Mode
    // =====================================================
    function fetchAtasanForEdit(roleApproved, targetAtasanNik, excludeNik) {
        if (!roleApproved) return;
        fetch('/karyawan/get-atasan?role_approved=' + encodeURIComponent(roleApproved) +
            '&exclude_nik=' + encodeURIComponent(excludeNik), {
            credentials: 'same-origin'
        }).then(function(r) { return r.json(); }).then(function(res) {
            var atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
            var target = atasanMap[roleApproved] || '';
            var html = '<option value="">Pilih Atasan (' + target + ')</option>';
            res.forEach(function(item) {
                var selected = item.nik === targetAtasanNik ? ' selected' : '';
                html += '<option value="' + item.nik + '"' + selected + '>' + item.nama_lengkap + ' (' + item.jabatan + ' - ' + item.posisi + ')</option>';
            });
            var select = formContainer.querySelector('[name="atasan_nik"]');
            if (select) {
                select.innerHTML = html;
                var wrapper = document.getElementById('atasan-wrapper');
                if (wrapper) wrapper.style.display = '';
            }
        });
    }

    // =====================================================
    // Cascading: Jabatan → Role Approved
    // =====================================================
    formContainer.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'jabatan') {
            var jabatan = e.target.value;
            var form = e.target.closest('form');
            var roleWrapper = form.querySelector('#role-approved-wrapper');
            var atasanWrapper = form.querySelector('#atasan-wrapper');
            var roleSelect = form.querySelector('[name="role_approved"]');
            var atasanSelect = form.querySelector('[name="atasan_nik"]');

            if (jabatan === 'Direktur' || jabatan === '') {
                roleWrapper.style.display = 'none';
                atasanWrapper.style.display = 'none';
                if (roleSelect) roleSelect.value = '';
                if (atasanSelect) atasanSelect.innerHTML = '<option value="">Pilih Atasan</option>';
                return;
            }
            roleWrapper.style.display = '';
            atasanWrapper.style.display = 'none';
            if (roleSelect) roleSelect.value = '';
            if (atasanSelect) atasanSelect.innerHTML = '<option value="">Pilih Atasan</option>';
        }
    });

    // =====================================================
    // Cascading: Role Approved → Atasan (via AJAX)
    // =====================================================
    formContainer.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'role_approved') {
            var roleApproved = e.target.value;
            var form = e.target.closest('form');
            var wrapper = form.querySelector('#atasan-wrapper');
            var select = form.querySelector('[name="atasan_nik"]');
            var nikInput = form.querySelector('[name="nik"]');
            var excludeNik = nikInput ? nikInput.value : '';

            if (!roleApproved || roleApproved === '') {
                wrapper.style.display = 'none';
                select.innerHTML = '<option value="">Pilih Atasan</option>';
                return;
            }
            fetch('/karyawan/get-atasan?role_approved=' + encodeURIComponent(roleApproved) +
                '&exclude_nik=' + encodeURIComponent(excludeNik), {
                credentials: 'same-origin'
            }).then(function(r) { return r.json(); }).then(function(res) {
                var atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
                var target = atasanMap[roleApproved] || '';
                var html = '<option value="">Pilih Atasan (' + target + ')</option>';
                res.forEach(function(k) {
                    html += '<option value="' + k.nik + '">' + k.nama_lengkap + ' (' + k.jabatan + ' - ' + k.posisi + ')</option>';
                });
                select.innerHTML = html;
                wrapper.style.display = '';
            });
        }
    });

    // =====================================================
    // Preview Foto
    // =====================================================
    document.addEventListener('click', function(e) {
        var fotoEl = e.target.closest('.foto-karyawan');
        if (!fotoEl) return;

        Swal.fire({
            imageUrl: fotoEl.getAttribute('src'),
            imageAlt: "Foto Karyawan",
            showConfirmButton: false,
            showCloseButton: true,
            width: "520px",
            backdrop: false
        });
    });

    // =====================================================
    // Delete Karyawan
    // =====================================================
    document.addEventListener('click', function(e) {
        var deleteBtn = e.target.closest('.delete-confirm');
        if (!deleteBtn) return;
        e.preventDefault();

        var form = deleteBtn.closest('form');

        Swal.fire({
            title: "Yakin data ini akan dihapus?",
            text: "Data karyawan beserta riwayat presensinya akan dihapus permanen.",
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
        var nik = form.querySelector('[name="nik"]').value;
        var nama = form.querySelector('[name="nama_lengkap"]').value;
        var jabatan = form.querySelector('[name="jabatan"]').value;
        var posisi = form.querySelector('[name="posisi"]').value;
        var unit = form.querySelector('[name="unit"]').value;
        var no_hp = form.querySelector('[name="no_hp"]').value;

        if (!nik) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "NIK tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="nik"]').focus();
            return;
        }
        if (!nama) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Nama lengkap tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="nama_lengkap"]').focus();
            return;
        }
        if (!jabatan) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Jabatan tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="jabatan"]').focus();
            return;
        }
        if (!posisi) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Posisi harus diisi.", backdrop: false });
            form.querySelector('[name="posisi"]').focus();
            return;
        }
        if (!unit) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Unit perusahaan harus dipilih.", backdrop: false });
            form.querySelector('[name="unit"]').focus();
            return;
        }
        if (!no_hp) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Nomor HP tidak boleh kosong.", backdrop: false });
            form.querySelector('[name="no_hp"]').focus();
            return;
        }
        var password = form.querySelector('[name="password"]');
        if (password && password.required && !password.value) {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Password tidak boleh kosong.", backdrop: false });
            password.focus();
            return;
        }
    });

    if (window.lucide) lucide.createIcons();

});
</script>
@endpush
