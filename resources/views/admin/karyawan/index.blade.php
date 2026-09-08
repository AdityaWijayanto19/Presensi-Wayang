@extends('layouts.admin.app')

@section('content')

    <x-app.page-header title="Data Karyawan" pretitle="WAG - Presensi Digital" />

    <x-app.page-body>

        <div class="bg-white rounded-md shadow-sm border border-slate-200">

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

                {{-- ================================================== --}}
                {{-- Button Tambah --}}
                {{-- ================================================== --}}
                @can('karyawan-create')
                <div class="mb-2">

                    <a href="#"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                        id="btnTambahkaryawan">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round">

                            <path stroke="none"
                                d="M0 0h24v24H0z"
                                fill="none" />

                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />

                            <path d="M16 19h6" />

                            <path d="M19 16v6" />

                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />

                        </svg>

                        Tambah Data Karyawan

                    </a>

                </div>
                @endcan

                {{-- ================================================== --}}
                {{-- Filter --}}
                {{-- ================================================== --}}
                <form
                    action="/panel/karyawan"
                    method="GET">

                    <div class="grid grid-cols-12 gap-2 mb-2">

                        <div class="col-span-12 md:col-span-4">

                            <input
                                type="text"
                                name="nama_karyawan"
                                id="nama_karyawan"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Cari Karyawan"
                                value="{{ Request('nama_karyawan') }}"
                                autocomplete="off">

                        </div>

                        <div class="col-span-12 md:col-span-2">
                            <select name="jabatan_filter" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                                <option value="">Semua Jabatan</option>
                                <option value="Intern" {{ Request('jabatan_filter')=='Intern'?'selected':'' }}>Intern</option>
                                <option value="Staff" {{ Request('jabatan_filter')=='Staff'?'selected':'' }}>Staff</option>
                                <option value="SPV" {{ Request('jabatan_filter')=='SPV'?'selected':'' }}>SPV (Supervisor)</option>
                                <option value="Manager" {{ Request('jabatan_filter')=='Manager'?'selected':'' }}>Manager</option>
                                <option value="GM" {{ Request('jabatan_filter')=='GM'?'selected':'' }}>GM</option>
                                <option value="Direktur" {{ Request('jabatan_filter')=='Direktur'?'selected':'' }}>Direktur</option>
                            </select>
                        </div>

                        <div class="col-span-12 md:col-span-2">

                            <select
                                name="unit"
                                id="unit_search"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                <option value="">
                                    Semua Unit
                                </option>

                                @foreach ($unitperusahaan as $u)

                                    <option
                                        value="{{ $u->unit }}"
                                        {{ Request('unit') == $u->unit ? 'selected' : '' }}>

                                        {{ $u->unit }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-span-12 md:col-span-2">

                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">

                                    <circle
                                        cx="10.5"
                                        cy="10.5"
                                        r="7.5" />

                                    <line
                                        x1="21"
                                        y1="21"
                                        x2="15.8"
                                        y2="15.8" />

                                </svg>

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

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIK</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Posisi</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Atasan</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No. HP</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Foto</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>

                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider" width="170">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        @foreach ($karyawan as $k)

                            @php
                                $path = asset('storage/uploads/karyawan/' . $k->foto);
                            @endphp

                            <tr class="hover:bg-slate-50">

                                <td class="px-3 py-2 text-sm">
                                    {{ $loop->iteration + $karyawan->firstItem() - 1 }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $k->nik }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $k->nama_lengkap }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    @if($k->jabatan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $k->jabatan=='Direktur' ? 'bg-red-100 text-red-700' : ($k->jabatan=='GM' ? 'bg-yellow-100 text-yellow-700' : ($k->jabatan=='Manager' ? 'bg-cyan-100 text-cyan-700' : ($k->jabatan=='SPV' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'))) }}">{{ $k->jabatan }}{{ $k->jabatan=='GM' ? ' (General Manager)' : ($k->jabatan=='SPV' ? ' (Supervisor)' : '') }}</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $k->posisi }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    @if($k->jabatan=='Direktur')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Langsung Admin</span><br><small class="text-slate-500">Tidak ada atasan</small>
                                    @else
                                        {{ $k->atasan_nama ?? '—' }}
                                        @if(!empty($k->atasan_jabatan))<br><small class="text-slate-500">{{ $k->atasan_jabatan }}</small>@endif
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $k->no_hp }}
                                </td>

                                {{-- ================================================== --}}
                                {{-- Foto --}}
                                {{-- ================================================== --}}
                                <td class="px-3 py-2 text-sm">

                                    @if ($k->foto == 'nophoto.png')

                                        <img
                                            src="{{ asset('assets/img/nophoto.png') }}"
                                            class="w-8 h-8 rounded-full foto-karyawan"
                                            style="cursor:pointer;"
                                            alt="Foto Default">

                                    @else

                                        <img
                                            src="{{ $path }}?v={{ time() }}"
                                            class="w-8 h-8 rounded-full foto-karyawan"
                                            style="cursor:pointer;"
                                            alt="{{ $k->nama_lengkap }}">

                                    @endif

                                </td>

                                <td class="px-3 py-2 text-sm">
                                    {{ $k->perusahaan }}
                                </td>

                                {{-- ================================================== --}}
                                {{-- Actions --}}
                                {{-- ================================================== --}}
                                <td class="px-3 py-2 text-sm">

                                    <div class="flex flex-wrap gap-1">

                                        {{-- ================= Edit ================= --}}
                                        @can('karyawan-edit')
                                        <a href="#"
                                            class="inline-flex items-center gap-2 bg-cyan-500 text-white px-2 py-1 rounded-md hover:bg-cyan-600 transition-colors text-xs font-medium edit"
                                            nik="{{ $k->nik }}"
                                            page="{{ request()->get('page',1) }}">

                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18"
                                                height="18"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">

                                                <path stroke="none"
                                                    d="M0 0h24v24H0z"
                                                    fill="none"/>

                                                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>

                                                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415"/>

                                                <path d="M16 5l3 3"/>

                                            </svg>

                                        </a>
                                        @endcan

                                        {{-- ================= Reset Password ================= --}}
                                        @can('karyawan-edit')
                                        <form action="/karyawan/{{ $k->nik }}/resetpassword"
                                            method="POST"
                                            class="inline">

                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-2 bg-yellow-500 text-white px-2 py-1 rounded-md hover:bg-yellow-600 transition-colors text-xs font-medium reset-password-confirm">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round">

                                                    <path stroke="none"
                                                        d="M0 0h24v24H0z"
                                                        fill="none"/>

                                                    <path d="M3.06 13a9 9 0 1 0 .49 -4.087"/>

                                                    <path d="M3 4.001v5h5"/>

                                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>

                                                </svg>

                                            </button>

                                        </form>
                                        @endcan

                                        {{-- ================= Delete ================= --}}
                                        @can('karyawan-delete')
                                        <form action="/karyawan/{{ $k->nik }}/delete"
                                            method="POST"
                                            class="inline">

                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-2 bg-red-600 text-white px-2 py-1 rounded-md hover:bg-red-700 transition-colors text-xs font-medium delete-confirm">

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor">

                                                    <path stroke="none"
                                                        d="M0 0h24v24H0z"
                                                        fill="none"/>

                                                    <path d="M20 6a1 1 0 0 1 .117 1.993l-.117 .007h-.081l-.919 11a3 3 0 0 1 -2.824 2.995l-.176 .005h-8c-1.598 0 -2.904 -1.249 -2.992 -2.75l-.005 -.167l-.923 -11.083h-.08a1 1 0 0 1 -.117 -1.993l.117 -.007zm-10 4a1 1 0 0 0 -1 1v6a1 1 0 0 0 2 0v-6a1 1 0 0 0 -1 -1m4 0a1 1 0 0 0 -1 1v6a1 1 0 0 0 2 0v-6a1 1 0 0 0 -1 -1"/>

                                                    <path d="M14 2a2 2 0 0 1 2 2a1 1 0 0 1 -1.993 .117l-.007 -.117h-4l-.007 .117a1 1 0 0 1 -1.993 -.117a2 2 0 0 1 1.85 -1.995l.15 -.005z"/>

                                                </svg>

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

        </div>

    </x-app.page-body>

    {{-- ================================================== --}}
    {{-- Modal Tambah Karyawan --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-inputkaryawan" title="Tambah Data Karyawan">

        <form
            action="/karyawan/store"
            method="POST"
            id="formKaryawan"
            enctype="multipart/form-data">

            @csrf

            {{-- NIK --}}
            <div class="relative mb-2">

                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">

                        <path stroke="none"
                            d="M0 0h24v24H0z"
                            fill="none"/>

                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/>
                        <path d="M8 13h1v3h-1"/>
                        <path d="M12 13v3"/>
                        <path d="M15 13h1v3h-1"/>

                    </svg>
                </span>

                <input
                    type="text"
                    class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="nik"
                    id="nik"
                    placeholder="NIK"
                    autocomplete="off">

            </div>

            {{-- Nama --}}
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

                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>

                        </svg>

                </span>

                <input
                    type="text"
                    class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="nama_lengkap"
                    id="nama_lengkap"
                    placeholder="Nama Lengkap"
                    autocomplete="off">

            </div>

            {{-- Jabatan --}}
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

                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1l0 -10"/>
                            <path d="M7 20l10 0"/>
                            <path d="M9 16l0 4"/>
                            <path d="M15 16l0 4"/>
                            <path d="M8 12l3 -3l2 2l3 -3"/>

                        </svg>

                </span>

                <input
                    type="text"
                    name="posisi"
                    id="posisi_input"
                    class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Posisi (contoh: Staff Accounting)">

            </div>

            {{-- Jabatan (Dropdown) --}}
            <div class="space-y-1 mb-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                <select name="jabatan" id="jabatan" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white" required>
                    <option value="">Pilih Jabatan</option>
                    <option value="Intern">Intern</option>
                    <option value="Staff">Staff</option>
                    <option value="SPV">SPV (Supervisor)</option>
                    <option value="Manager">Manager</option>
                    <option value="GM">GM (General Manager)</option>
                    <option value="Direktur">Direktur</option>
                </select>
            </div>

            {{-- Role Approved (Dropdown) --}}
            <div class="space-y-1 mb-2" id="role-approved-wrapper" style="display:none;">
                <label class="block text-sm font-medium text-slate-700 mb-1">Role Approved</label>
                <select name="role_approved" id="role_approved" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Pilih Role Approved</option>
                    <option value="Staff">Staff</option>
                    <option value="Manager">Manager</option>
                    <option value="GM">GM (General Manager)</option>
                    <option value="Direktur">Direktur</option>
                </select>
                <small class="text-slate-500">Role yang berwenang menyetujui WFH/Lembur karyawan ini.</small>
            </div>

            {{-- Atasan (dinamis berdasarkan Role Approved) --}}
            <div class="space-y-1 mb-2" id="atasan-wrapper" style="display:none;">
                <select name="atasan_nik" id="atasan_nik" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Pilih Atasan</option>
                </select>
                <small class="text-slate-500">Atasan muncul sesuai Role Approved yang dipilih.</small>
            </div>

            {{-- Unit --}}
            <div class="space-y-1 mb-2">

                <select
                    name="unit"
                    id="unit"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                    <option value="">
                        Pilih Unit
                    </option>

                    @foreach ($unitperusahaan as $u)

                        <option value="{{ $u->unit }}">

                            {{ $u->unit }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Nomor HP --}}
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

                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"/>
                            <path d="M15 6h6m-3 -3v6"/>

                        </svg>

                </span>

                <input
                    type="text"
                    class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    name="no_hp"
                    id="no_hp"
                    placeholder="No. HP"
                    autocomplete="off">

            </div>

            {{-- Upload Foto --}}
            <div class="space-y-1 mb-2">

                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Upload Foto
                </label>

                <input
                    type="file"
                    name="foto"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">

            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                Simpan Data

            </button>

        </form>

    </x-app.modal>

    {{-- ================================================== --}}
    {{-- Modal Edit --}}
    {{-- ================================================== --}}
    <x-app.modal id="modal-editkaryawan" title="Edit Data Karyawan">

        <div id="loadeditform">

            {{-- Form edit akan dimuat melalui AJAX --}}

        </div>

    </x-app.modal>

@endsection

@push('myscript')

<script>

$(function () {

    // =====================================================
    // Modal Tambah Karyawan
    // =====================================================

    $("#btnTambahkaryawan").click(function () {

        window.dispatchEvent(new CustomEvent('open-modal-modal-inputkaryawan'));

    });


    // =====================================================
    // Modal Edit Karyawan
    // =====================================================

    $(".edit").click(function () {

        let nik = $(this).attr("nik");
        let page = $(this).attr("page");

        $.ajax({

            type: "POST",

            url: "/karyawan/edit",

            data: {
                _token: "{{ csrf_token() }}",
                nik: nik,
                page: page
            },

            cache: false,

            success: function (respond) {

                $("#loadeditform").html(respond);

                window.dispatchEvent(new CustomEvent('open-modal-modal-editkaryawan'));

            }

        });

    });


    // =====================================================
    // Jabatan change -> show Role Approved
    // =====================================================
    $(document).on("change", "#jabatan", function(){
        let jabatan = $(this).val();
        let roleWrapper = $("#role-approved-wrapper");
        let atasanWrapper = $("#atasan-wrapper");
        if(jabatan==="Direktur" || jabatan===""){
            roleWrapper.hide();
            atasanWrapper.hide();
            $("#atasan_nik").html("<option value=\"\">Pilih Atasan</option>");
            return;
        }
        roleWrapper.show();
        // Reset atasan when jabatan changes
        atasanWrapper.hide();
        $("#role_approved").val("");
        $("#atasan_nik").html("<option value=\"\">Pilih Atasan</option>");
    });

    // =====================================================
    // Role Approved change -> fetch Atasan
    // =====================================================
    $(document).on("change", "#role_approved", function(){
        let roleApproved = $(this).val();
        let wrapper = $("#atasan-wrapper");
        let select = $("#atasan_nik");
        if(!roleApproved || roleApproved===""){ wrapper.hide(); select.html("<option value=\"\">Pilih Atasan</option>"); return; }
        $.ajax({ type:"GET", url:"/karyawan/get-atasan", data:{role_approved:roleApproved}, success:function(res){
            let atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
            let target = atasanMap[roleApproved] || '';
            let html = "<option value=\"\">Pilih Atasan ("+ target +")</option>";
            res.forEach(function(k){ html += "<option value=\""+k.nik+"\">"+k.nama_lengkap+" ("+k.jabatan+" - "+k.posisi+")</option>"; });
            select.html(html); wrapper.show();
        }});
    });

    // =====================================================
    // Edit: Role Approved change -> fetch Atasan
    // =====================================================
    $(document).on("change", "#edit_role_approved", function(){
        let roleApproved = $(this).val();
        let wrapper = $("#edit_atasan_wrapper");
        let select = $("#edit_atasan_nik");
        let nik = $("#edit_atasan_wrapper").closest("form").find("input[name=nik]").val() || '';
        if(!roleApproved || roleApproved===""){ wrapper.hide(); select.html("<option value=\"\">Pilih Atasan</option>"); return; }
        $.ajax({ type:"GET", url:"/karyawan/get-atasan", data:{role_approved:roleApproved, exclude_nik: nik}, success:function(res){
            let atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
            let target = atasanMap[roleApproved] || '';
            let html = "<option value=\"\">Pilih Atasan ("+ target +")</option>";
            res.forEach(function(k){ html += "<option value=\""+k.nik+"\">"+k.nama_lengkap+" ("+k.jabatan+" - "+k.posisi+")</option>"; });
            select.html(html); wrapper.show();
        }});
    });

    // =====================================================
    // Preview Foto
    // =====================================================

    $(document).on("click", ".foto-karyawan", function () {

        Swal.fire({

            imageUrl: $(this).attr("src"),

            imageAlt: "Foto Karyawan",

            showConfirmButton: false,

            showCloseButton: true,

            width: "520px",

            backdrop: false

        });

    });


    // =====================================================
    // Reset Password
    // =====================================================

    $(".reset-password-confirm").click(function (e) {

        e.preventDefault();

        let form = $(this).closest("form");

        Swal.fire({

            title: "Reset Password?",

            text: "Password karyawan akan direset menjadi 12345.",

            icon: "warning",

            showCancelButton: true,

            confirmButtonColor: "#3085d6",

            cancelButtonColor: "#d33",

            confirmButtonText: "Ya, Reset!",

            cancelButtonText: "Batal",

            backdrop: false

        }).then((result) => {

            if (result.isConfirmed) {

                form.submit();

            }

        });

    });


    // =====================================================
    // Delete Karyawan
    // =====================================================

    $(".delete-confirm").click(function (e) {

        e.preventDefault();

        let form = $(this).closest("form");

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

        }).then((result) => {

            if (result.isConfirmed) {

                form.submit();

            }

        });

    });


    // =====================================================
    // Validasi Form Tambah
    // =====================================================

    $("#formKaryawan").submit(function () {

        let nik = $("#nik").val();

        let nama = $("#nama_lengkap").val();

        let jabatan = $("#jabatan").val();

        let unit = $("#formKaryawan").find("#unit").val();

        let no_hp = $("#no_hp").val();


        if (nik == "") {

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "NIK tidak boleh kosong.",

                backdrop: false

            });

            $("#nik").focus();

            return false;

        }


        if (nama == "") {

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Nama lengkap tidak boleh kosong.",

                backdrop: false

            });

            $("#nama_lengkap").focus();

            return false;

        }


        if (jabatan == "") {

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Jabatan tidak boleh kosong.",

                backdrop: false

            });

            $("#jabatan").focus();

            return false;

        }

        let posisi = $("#posisi_input").val();
        if (posisi == "") {
            Swal.fire({ icon: "warning", title: "Oops...", text: "Posisi harus diisi.", backdrop: false });
            $("#posisi_input").focus();
            return false;
        }

        if (unit == "") {

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Unit perusahaan harus dipilih.",

                backdrop: false

            });

            $("#unit").focus();

            return false;

        }


        if (no_hp == "") {

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Nomor HP tidak boleh kosong.",

                backdrop: false

            });

            $("#no_hp").focus();

            return false;

        }

    });

});

</script>

@endpush
