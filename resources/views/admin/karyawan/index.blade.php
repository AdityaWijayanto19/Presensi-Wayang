@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Karyawan')

<x-admin.page-body>

    <x-admin.card>

        <div class="p-3">

            {{-- ================================================== --}}
            {{-- Button Tambah --}}
            {{-- ================================================== --}}
            @can('karyawan-create')
                <div class="mb-2">
                    <x-admin.button variant="primary" icon="user-plus" href="#" id="btnTambahkaryawan">Tambah Data
                        Karyawan</x-admin.button>
                </div>
            @endcan

            {{-- ================================================== --}}
            {{-- Filter --}}
            {{-- ================================================== --}}
            <form action="/panel/karyawan" method="GET">
                <div class="grid grid-cols-12 gap-2 mb-2">
                    <div class="col-span-12 md:col-span-4">
                        <x-admin.input name="nama_karyawan" id="nama_karyawan" placeholder="Cari Karyawan"
                            value="{{ Request('nama_karyawan') }}" autocomplete="off" />
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.select name="jabatan_filter" placeholder="Semua Jabatan">
                            <option value="Intern" {{ Request('jabatan_filter') == 'Intern' ? 'selected' : '' }}>Intern
                            </option>
                            <option value="Staff" {{ Request('jabatan_filter') == 'Staff' ? 'selected' : '' }}>Staff
                            </option>
                            <option value="SPV" {{ Request('jabatan_filter') == 'SPV' ? 'selected' : '' }}>Supervisor
                            </option>
                            <option value="Manager" {{ Request('jabatan_filter') == 'Manager' ? 'selected' : '' }}>
                                Manager
                            </option>
                            <option value="GM" {{ Request('jabatan_filter') == 'GM' ? 'selected' : '' }}>GM</option>
                            <option value="Direktur" {{ Request('jabatan_filter') == 'Direktur' ? 'selected' : '' }}>
                                Direktur
                            </option>
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.select name="unit" searchable>
                            <option value="">Pilih Unit Perusahaan</option>
                            @foreach ($unitperusahaan as $u)
                                <option value="{{ $u->unit }}" {{ Request('unit') == $u->unit ? 'selected' : '' }}>
                                    {{ $u->unit }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <x-admin.button variant="primary" icon="search" type="submit" block>Cari Data</x-admin.button>
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
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                NIK</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Nama</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Jabatan</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Posisi</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Atasan</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                No. HP</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Foto</th>
                            <th
                                class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                Unit Perusahaan</th>
                            <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider"
                                width="170">Actions</th>
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
                                    @if ($k->jabatan)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $k->jabatan == 'Direktur' ? 'bg-red-100 text-red-700' : ($k->jabatan == 'GM' ? 'bg-yellow-100 text-yellow-700' : ($k->jabatan == 'Manager' ? 'bg-cyan-100 text-cyan-700' : ($k->jabatan == 'Supervisor' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'))) }}">{{ $k->jabatan }}</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->posisi }}
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    @if ($k->jabatan == 'Direktur')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Langsung
                                            Admin</span><br><small class="text-slate-500">Manager HRGA</small>
                                    @else
                                        {{ $k->atasan->nama_lengkap ?? 'Manager HRGA' }}
                                        @if (!empty($k->atasan->jabatan))
                                            <br><small class="text-slate-500">{{ $k->atasan->jabatan }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    {{ $k->no_hp }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    @if ($k->foto == 'nophoto.png')
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center foto-karyawan"
                                            style="cursor:pointer;">
                                            <i data-lucide="user" style="width:16px;height:16px;"></i>
                                        </div>
                                    @else
                                        <img src="{{ $path }}?v={{ time() }}"
                                            class="w-8 h-8 rounded-full foto-karyawan" style="cursor:pointer;"
                                            alt="{{ $k->nama_lengkap }}">
                                    @endif
                                </td>
                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->unitperusahaan->perusahaan ?? '' }}
                                </td>
                                <td class="px-2 py-1.5 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @can('karyawan-edit')
                                            <x-admin.button variant="edit" icon="square-pen" size="sm" href="#"
                                                class="edit" nik="{{ $k->nik }}"
                                                page="{{ request()->get('page', 1) }}" />
                                        @endcan
                                        @can('karyawan-delete')
                                            <form action="/karyawan/{{ $k->nik }}/delete" method="POST"
                                                class="inline">
                                                @csrf
                                                <x-admin.button variant="danger" icon="trash-2" size="sm"
                                                    type="submit" class="delete-confirm" />
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
    $karyawanJson = $karyawan
        ->map(
            fn($k) => [
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
            ],
        )
        ->toJson();
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
            if (window.lucide) lucide.createIcons();
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
            var k = karyawanData.find(function(item) {
                return item.nik == nik;
            });
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
            form.querySelector('[name="role_approved"]').value = k.role_approved || '';
            form.querySelector('[name="no_hp"]').value = k.no_hp;
            form.querySelector('[name="password"]').placeholder = 'Kosongkan jika tidak diubah';
            form.querySelector('[name="password"]').removeAttribute('required');

            var fotoLama = form.querySelector('[name="foto_lama"]');
            if (fotoLama) fotoLama.value = k.foto;

            var cropPreview = form.querySelector('#crop-preview-foto');
            var cropPreviewImg = form.querySelector('#crop-preview-foto-img');
            var cropBtnText = form.querySelector('[data-crop-btn-text="foto"]');
            if (cropPreview && cropPreviewImg) {
                if (k.foto && k.foto !== 'nophoto.png') {
                    cropPreviewImg.src = '{{ asset('storage/uploads/karyawan/') }}/' + k.foto;
                    cropPreview.style.display = '';
                    if (cropBtnText) cropBtnText.textContent = 'Ganti Foto';
                } else {
                    cropPreviewImg.src = '';
                    cropPreview.style.display = 'none';
                    if (cropBtnText) cropBtnText.textContent = 'Pilih Foto';
                }
            }

            var btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = '<i data-lucide="save" style="width:16px;height:16px;"></i> Perbarui Data';

            formContainer.innerHTML = '';
            formContainer.appendChild(clone);
            if (window.lucide) lucide.createIcons();

            function dispatchSetValue(name, val) {
                formContainer.dispatchEvent(new CustomEvent('set-value', {
                    detail: {
                        name: name,
                        value: val
                    },
                    bubbles: true
                }));
            }

            function dispatchChangeEvent(name) {
                var el = formContainer.querySelector('[name="' + name + '"]');
                if (el) el.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }

            setTimeout(function() {
                dispatchSetValue('jabatan', k.jabatan || '');
                setTimeout(function() {
                    dispatchChangeEvent('jabatan');
                    setTimeout(function() {
                        if (k.role_approved) {
                            dispatchSetValue('role_approved', k.role_approved);
                            setTimeout(function() {
                                dispatchChangeEvent('role_approved');
                                fetchAtasanForEdit(k.role_approved, k
                                    .atasan_nik, k.nik);
                            }, 30);
                        }
                        dispatchSetValue('unit', k.unit || '');
                    }, 30);
                }, 30);
            }, 30);

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
                }).then(function(r) {
                return r.json();
            }).then(function(res) {
                var atasanMap = {
                    "Staff": "Manager",
                    "Manager": "GM",
                    "GM": "Direktur",
                    "Direktur": ""
                };
                var target = atasanMap[roleApproved] || '';
                var options = [{
                    value: '',
                    label: 'Pilih Atasan (' + target + ')'
                }];
                res.forEach(function(item) {
                    options.push({
                        value: item.nik,
                        label: item.nama_lengkap + ' (' + item.jabatan + ' - ' + item
                            .posisi + ')'
                    });
                });
                formContainer.dispatchEvent(new CustomEvent('options-updated', {
                    detail: {
                        name: 'atasan_nik',
                        options: options
                    },
                    bubbles: true
                }));
                setTimeout(function() {
                    formContainer.dispatchEvent(new CustomEvent('set-value', {
                        detail: {
                            name: 'atasan_nik',
                            value: targetAtasanNik || ''
                        },
                        bubbles: true
                    }));
                }, 30);
                var wrapper = document.getElementById('atasan-wrapper');
                if (wrapper) wrapper.style.display = '';
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

                if (jabatan === 'Direktur' || jabatan === '') {
                    roleWrapper.style.display = 'none';
                    atasanWrapper.style.display = 'none';
                    formContainer.dispatchEvent(new CustomEvent('set-value', {
                        detail: {
                            name: 'role_approved',
                            value: ''
                        },
                        bubbles: true
                    }));
                    formContainer.dispatchEvent(new CustomEvent('options-updated', {
                        detail: {
                            name: 'atasan_nik',
                            options: [{
                                value: '',
                                label: 'Pilih Atasan'
                            }]
                        },
                        bubbles: true
                    }));
                    formContainer.dispatchEvent(new CustomEvent('set-value', {
                        detail: {
                            name: 'atasan_nik',
                            value: ''
                        },
                        bubbles: true
                    }));
                    return;
                }
                roleWrapper.style.display = '';
                atasanWrapper.style.display = 'none';
                formContainer.dispatchEvent(new CustomEvent('set-value', {
                    detail: {
                        name: 'role_approved',
                        value: ''
                    },
                    bubbles: true
                }));
                formContainer.dispatchEvent(new CustomEvent('options-updated', {
                    detail: {
                        name: 'atasan_nik',
                        options: [{
                            value: '',
                            label: 'Pilih Atasan'
                        }]
                    },
                    bubbles: true
                }));
                formContainer.dispatchEvent(new CustomEvent('set-value', {
                    detail: {
                        name: 'atasan_nik',
                        value: ''
                    },
                    bubbles: true
                }));
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
                var nikInput = form.querySelector('[name="nik"]');
                var excludeNik = nikInput ? nikInput.value : '';

                if (!roleApproved || roleApproved === '') {
                    wrapper.style.display = 'none';
                    formContainer.dispatchEvent(new CustomEvent('options-updated', {
                        detail: {
                            name: 'atasan_nik',
                            options: [{
                                value: '',
                                label: 'Pilih Atasan'
                            }]
                        },
                        bubbles: true
                    }));
                    formContainer.dispatchEvent(new CustomEvent('set-value', {
                        detail: {
                            name: 'atasan_nik',
                            value: ''
                        },
                        bubbles: true
                    }));
                    return;
                }
                fetch('/karyawan/get-atasan?role_approved=' + encodeURIComponent(roleApproved) +
                    '&exclude_nik=' + encodeURIComponent(excludeNik), {
                        credentials: 'same-origin'
                    }).then(function(r) {
                    return r.json();
                }).then(function(res) {
                    var atasanMap = {
                        "Staff": "Manager",
                        "Manager": "GM",
                        "GM": "Direktur",
                        "Direktur": ""
                    };
                    var target = atasanMap[roleApproved] || '';
                    var options = [{
                        value: '',
                        label: 'Pilih Atasan (' + target + ')'
                    }];
                    res.forEach(function(k) {
                        options.push({
                            value: k.nik,
                            label: k.nama_lengkap + ' (' + k.jabatan + ' - ' + k
                                .posisi + ')'
                        });
                    });
                    formContainer.dispatchEvent(new CustomEvent('options-updated', {
                        detail: {
                            name: 'atasan_nik',
                            options: options
                        },
                        bubbles: true
                    }));
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
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "NIK tidak boleh kosong.",
                    backdrop: false
                });
                form.querySelector('[name="nik"]').focus();
                return;
            }
            if (!nama) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Nama lengkap tidak boleh kosong.",
                    backdrop: false
                });
                form.querySelector('[name="nama_lengkap"]').focus();
                return;
            }
            if (!jabatan) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Jabatan tidak boleh kosong.",
                    backdrop: false
                });
                form.querySelector('[name="jabatan"]').focus();
                return;
            }
            if (!posisi) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Posisi harus diisi.",
                    backdrop: false
                });
                form.querySelector('[name="posisi"]').focus();
                return;
            }
            if (!unit) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Unit perusahaan harus dipilih.",
                    backdrop: false
                });
                form.querySelector('[name="unit"]').focus();
                return;
            }
            if (!no_hp) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Nomor HP tidak boleh kosong.",
                    backdrop: false
                });
                form.querySelector('[name="no_hp"]').focus();
                return;
            }
            var password = form.querySelector('[name="password"]');
            if (password && password.required && !password.value) {
                e.preventDefault();
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Password tidak boleh kosong.",
                    backdrop: false
                });
                password.focus();
                return;
            }
        });

        // =====================================================
        // Image Crop — Event Delegation
        // =====================================================
        (function() {
            var cropInstances = {};

            function getOrCreateCropper(id, imgSrc, aspectRatio) {
                var imgEl = document.getElementById('crop-img-' + id);
                if (!imgEl) return null;
                imgEl.src = imgSrc;

                if (cropInstances[id]) {
                    cropInstances[id].destroy();
                }

                cropInstances[id] = new Cropper(imgEl, {
                    viewMode: 1,
                    aspectRatio: aspectRatio || 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                    guides: false,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                });

                return cropInstances[id];
            }

            function openModal(id) {
                var modal = document.getElementById('crop-modal-' + id);
                if (modal) {
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal(id, keepFile) {
                var modal = document.getElementById('crop-modal-' + id);
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
                if (cropInstances[id]) {
                    cropInstances[id].destroy();
                    cropInstances[id] = null;
                }
                if (!keepFile) {
                    var fi = document.getElementById('crop-input-' + id);
                    if (fi) fi.value = '';
                }
            }

            function saveCrop(id) {
                var inst = cropInstances[id];
                if (!inst) return;

                var canvas = inst.getCroppedCanvas({
                    width: 800,
                    height: 800
                });
                canvas.toBlob(function(blob) {
                    if (!blob) return;

                    var file = new File([blob], 'cropped-foto.webp', {
                        type: 'image/webp'
                    });
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    var fi = document.getElementById('crop-input-' + id);
                    if (fi) fi.files = dt.files;

                    var preview = document.getElementById('crop-preview-' + id);
                    var previewImg = document.getElementById('crop-preview-' + id + '-img');
                    if (preview && previewImg) {
                        previewImg.src = URL.createObjectURL(blob);
                        preview.style.display = '';
                        previewImg.onload = function() {
                            URL.revokeObjectURL(previewImg.src);
                        };
                    }

                    var btnText = document.querySelector('[data-crop-btn-text="' + id + '"]');
                    if (btnText) btnText.textContent = 'Ganti Foto';

                    closeModal(id, true);
                }, 'image/webp', 0.9);
            }

            function resetCrop(id) {
                var fi = document.getElementById('crop-input-' + id);
                if (fi) fi.value = '';
                var preview = document.getElementById('crop-preview-' + id);
                var previewImg = document.getElementById('crop-preview-' + id + '-img');
                if (preview && previewImg) {
                    previewImg.src = '';
                    preview.style.display = 'none';
                }
                var btnText = document.querySelector('[data-crop-btn-text="' + id + '"]');
                if (btnText) btnText.textContent = 'Pilih Foto';
            }

            document.addEventListener('change', function(e) {
                var input = e.target.closest('[data-crop-input]');
                if (!input) return;
                var id = input.getAttribute('data-crop-input');
                var file = input.files && input.files[0];
                if (!file) return;

                var reader = new FileReader();
                reader.onload = function(ev) {
                    var img = document.getElementById('crop-img-' + id);
                    if (img) {
                        img.src = ev.target.result;
                        openModal(id);
                        getOrCreateCropper(id, ev.target.result, 1);
                    }
                };
                reader.readAsDataURL(file);
            });

            document.addEventListener('click', function(e) {
                var trigger = e.target.closest('[data-crop-trigger]');
                if (trigger) {
                    var id = trigger.getAttribute('data-crop-trigger');
                    var fi = document.getElementById('crop-input-' + id);
                    if (fi) fi.click();
                    return;
                }

                var cancel = e.target.closest('[data-crop-cancel]');
                if (cancel) {
                    closeModal(cancel.getAttribute('data-crop-cancel'));
                    return;
                }

                var save = e.target.closest('[data-crop-save]');
                if (save) {
                    saveCrop(save.getAttribute('data-crop-save'));
                    return;
                }

                var reset = e.target.closest('[data-crop-reset]');
                if (reset) {
                    resetCrop(reset.getAttribute('data-crop-reset'));
                    return;
                }
            }, true);
        })();

        if (window.lucide) lucide.createIcons();

    });
</script>
@endpush
