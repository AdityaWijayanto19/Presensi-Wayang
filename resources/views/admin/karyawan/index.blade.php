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
                                class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Cari Karyawan"
                                value="{{ Request('nama_karyawan') }}"
                                autocomplete="off">

                        </div>

                        <div class="col-span-12 md:col-span-2">
                            <select name="jabatan_filter" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
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
                                class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

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

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">NIK</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Nama</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Jabatan</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Posisi</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Atasan</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">No. HP</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Foto</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>

                                <th class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider" width="170">
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
                                        {{ $k->atasan_nama ?? '—' }}
                                        @if(!empty($k->atasan_jabatan))<br><small class="text-slate-500">{{ $k->atasan_jabatan }}</small>@endif
                                    @endif
                                </td>

                                <td class="px-2 py-1.5 text-xs">
                                    {{ $k->no_hp }}
                                </td>

                                {{-- ================================================== --}}
                                {{-- Foto --}}
                                {{-- ================================================== --}}
                                <td class="px-2 py-1.5 text-xs">

                                    @if ($k->foto == 'nophoto.png')

                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center foto-karyawan" style="cursor:pointer;">
                                            <i data-lucide="user" style="width:16px;height:16px;"></i>
                                        </div>

                                    @else

                                        <img
                                            src="{{ $path }}?v={{ time() }}"
                                            class="w-8 h-8 rounded-full foto-karyawan"
                                            style="cursor:pointer;"
                                            alt="{{ $k->nama_lengkap }}">

                                    @endif

                                </td>

                                <td class="px-2 py-1.5 text-xs truncate-cell">
                                    {{ $k->unitperusahaan->perusahaan ?? '' }}
                                </td>

                                {{-- ================================================== --}}
                                {{-- Actions --}}
                                {{-- ================================================== --}}
                                <td class="px-2 py-1.5 text-xs">

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

        </x-admin.card>

    </x-admin.page-body>

    {{-- ================================================== --}}
    {{-- Modal Tambah Karyawan --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-inputkaryawan" title="Tambah Data Karyawan">

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
                <select name="jabatan" id="jabatan" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white" required>
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
                <select name="role_approved" id="role_approved" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
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
                <select name="atasan_nik" id="atasan_nik" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                    <option value="">Pilih Atasan</option>
                </select>
                <small class="text-slate-500">Atasan muncul sesuai Role Approved yang dipilih.</small>
            </div>

            {{-- Unit --}}
            <div class="space-y-1 mb-2">

                <select
                    name="unit"
                    id="unit"
                    class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

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

            {{-- Password --}}
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
                        <path d="M12 3a2 2 0 0 0 -2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2h-14a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2h-1"/>
                        <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>

                    </svg>
                </span>

                <input
                    type="password"
                    name="password"
                    id="password_input"
                    class="w-full rounded-md border border-slate-300 pl-8 pr-10 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Password"
                    required>

                <button type="button"
                    onclick="togglePassword('password_input', this)"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-closed" style="display:none;">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M21 21l-6 -6l-5 -5"/>
                        <path d="M3 3l18 18"/>
                        <path d="M10.5 10.5a2 2 0 1 0 2.936 2.942"/>
                        <path d="M4.487 4.489c-1.168 .735 -1.988 1.687 -2.487 2.511c2.4 -4 5.4 -6 9 -6c1.036 0 2.032 .18 2.968 .512"/>
                        <path d="M19.5 15c.847 .543 1.555 1.159 2 1.814"/>
                        <path d="M3 3l18 18"/>
                    </svg>
                </button>

            </div>

            {{-- Upload Foto --}}
            <div class="space-y-1 mb-2">

                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Upload Foto
                </label>

                <input
                    type="file"
                    name="foto"
                    class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">

            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                Simpan Data

            </button>

        </form>

    </x-admin.modal>

    {{-- ================================================== --}}
    {{-- Modal Edit --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-editkaryawan" title="Edit Data Karyawan">

        <div id="loadeditform">

            {{-- Form edit akan dimuat melalui AJAX --}}

        </div>

    </x-admin.modal>

@endsection

@push('myscript')

<script>

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // Toggle Password Visibility
    // =====================================================
    window.togglePassword = function (inputId, btn) {
        var input = document.getElementById(inputId);
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
    // Modal Tambah Karyawan
    // =====================================================

    document.getElementById('btnTambahkaryawan').addEventListener('click', function () {

        window.dispatchEvent(new CustomEvent('open-modal-modal-inputkaryawan'));

    });


    // =====================================================
    // Modal Edit Karyawan
    // =====================================================

    document.querySelector('tbody').addEventListener('click', function (e) {

        var editBtn = e.target.closest('.edit');

        if (!editBtn) return;

        e.preventDefault();

        var nik = editBtn.getAttribute('nik');
        var page = editBtn.getAttribute('page');

        fetch('/karyawan/edit', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },

            body: new URLSearchParams({
                nik: nik,
                page: page
            })

        }).then(function (r) { return r.text(); }).then(function (html) {

            document.getElementById('loadeditform').innerHTML = html;

            window.dispatchEvent(new CustomEvent('open-modal-modal-editkaryawan'));

        });

    });


    // =====================================================
    // Jabatan change -> show Role Approved
    // =====================================================
    document.getElementById('jabatan').addEventListener('change', function () {
        var jabatan = this.value;
        var roleWrapper = document.getElementById('role-approved-wrapper');
        var atasanWrapper = document.getElementById('atasan-wrapper');
        if (jabatan === "Direktur" || jabatan === "") {
            roleWrapper.style.display = 'none';
            atasanWrapper.style.display = 'none';
            document.getElementById('atasan_nik').innerHTML = '<option value="">Pilih Atasan</option>';
            return;
        }
        roleWrapper.style.display = '';
        // Reset atasan when jabatan changes
        atasanWrapper.style.display = 'none';
        document.getElementById('role_approved').value = '';
        document.getElementById('atasan_nik').innerHTML = '<option value="">Pilih Atasan</option>';
    });

    // =====================================================
    // Role Approved change -> fetch Atasan
    // =====================================================
    document.getElementById('role_approved').addEventListener('change', function () {
        var roleApproved = this.value;
        var wrapper = document.getElementById('atasan-wrapper');
        var select = document.getElementById('atasan_nik');
        if (!roleApproved || roleApproved === "") {
            wrapper.style.display = 'none';
            select.innerHTML = '<option value="">Pilih Atasan</option>';
            return;
        }
        fetch('/karyawan/get-atasan?role_approved=' + encodeURIComponent(roleApproved), {
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (res) {
            var atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
            var target = atasanMap[roleApproved] || '';
            var html = '<option value="">Pilih Atasan (' + target + ')</option>';
            res.forEach(function (k) {
                html += '<option value="' + k.nik + '">' + k.nama_lengkap + ' (' + k.jabatan + ' - ' + k.posisi + ')</option>';
            });
            select.innerHTML = html;
            wrapper.style.display = '';
        });
    });

    // =====================================================
    // Edit: Role Approved change -> fetch Atasan
    // =====================================================
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'edit_role_approved') {
            var roleApproved = e.target.value;
            var wrapper = document.getElementById('edit_atasan_wrapper');
            var select = document.getElementById('edit_atasan_nik');
            var editWrapper = document.getElementById('edit_atasan_wrapper');
            var editForm = editWrapper ? editWrapper.closest('form') : null;
            var nikInput = editForm ? editForm.querySelector('input[name="nik"]') : null;
            var nik = nikInput ? nikInput.value : '';
            if (!roleApproved || roleApproved === "") {
                wrapper.style.display = 'none';
                select.innerHTML = '<option value="">Pilih Atasan</option>';
                return;
            }
            fetch('/karyawan/get-atasan?role_approved=' + encodeURIComponent(roleApproved) + '&exclude_nik=' + encodeURIComponent(nik), {
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); }).then(function (res) {
                var atasanMap = {"Staff":"Manager","Manager":"GM","GM":"Direktur","Direktur":""};
                var target = atasanMap[roleApproved] || '';
                var html = '<option value="">Pilih Atasan (' + target + ')</option>';
                res.forEach(function (k) {
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

    document.addEventListener('click', function (e) {
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

    document.addEventListener('click', function (e) {

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

        }).then(function (result) {

            if (result.isConfirmed) {

                form.submit();

            }

        });

    });


    // =====================================================
    // Validasi Form Tambah
    // =====================================================

    document.getElementById('formKaryawan').addEventListener('submit', function (e) {

        var nik = document.getElementById('nik').value;

        var nama = document.getElementById('nama_lengkap').value;

        var jabatan = document.getElementById('jabatan').value;

        var unit = document.getElementById('unit').value;

        var no_hp = document.getElementById('no_hp').value;


        if (nik == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "NIK tidak boleh kosong.",

                backdrop: false

            });

            document.getElementById('nik').focus();

            return;

        }


        if (nama == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Nama lengkap tidak boleh kosong.",

                backdrop: false

            });

            document.getElementById('nama_lengkap').focus();

            return;

        }


        if (jabatan == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Jabatan tidak boleh kosong.",

                backdrop: false

            });

            document.getElementById('jabatan').focus();

            return;

        }

        var posisi = document.getElementById('posisi_input').value;
        if (posisi == "") {
            e.preventDefault();
            Swal.fire({ icon: "warning", title: "Oops...", text: "Posisi harus diisi.", backdrop: false });
            document.getElementById('posisi_input').focus();
            return;
        }

        if (unit == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Unit perusahaan harus dipilih.",

                backdrop: false

            });

            document.getElementById('unit').focus();

            return;

        }


        if (no_hp == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Nomor HP tidak boleh kosong.",

                backdrop: false

            });

            document.getElementById('no_hp').focus();

            return;

        }

        var password = document.getElementById('password_input').value;

        if (password == "") {

            e.preventDefault();

            Swal.fire({

                icon: "warning",

                title: "Oops...",

                text: "Password tidak boleh kosong.",

                backdrop: false

            });

            document.getElementById('password_input').focus();

            return;

        }

    });

});

</script>

@endpush
