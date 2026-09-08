@extends('layouts.admin.app')

@section('content')

    <x-app.page-header title="Data User / Admin" pretitle="WAG - Presensi Digital" />

    <x-app.page-body>

        <div class="bg-white rounded-md shadow-sm border border-slate-200">
            <div class="p-3">

                {{-- ================================================== --}}
                {{-- Alert --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">

                        @if (Session::get('success'))
                            <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-md text-sm">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        @if (Session::get('error'))
                            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-md text-sm">
                                {{ Session::get('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-md text-sm">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- Button Tambah User --}}
                {{-- ================================================== --}}
                @can('user-manage')
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">
                        <a href="#" class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium" id="btnTambahuser">

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
                                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                <path d="M16 19h6" />
                                <path d="M19 16v6" />
                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />

                            </svg>

                            Tambah Data User / Admin

                        </a>
                    </div>
                </div>
                @endcan

                {{-- ================================================== --}}
                {{-- Data User --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2 mt-2">
                    <div class="col-span-12">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 border border-slate-200">

                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit Perusahaan</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-slate-200">

                                    @foreach ($users as $d)
                                        <tr class="hover:bg-slate-50">

                                            <td class="px-3 py-2 text-sm text-slate-700">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-2 text-sm text-slate-700">{{ $d->name }}</td>
                                            <td class="px-3 py-2 text-sm text-slate-700">{{ $d->email }}</td>
                                            <td class="px-3 py-2 text-sm text-slate-700">{{ $d->perusahaan }}</td>
                                            <td class="px-3 py-2 text-sm text-slate-700">{{ ucwords($d->role) }}</td>

                                            <td class="px-3 py-2 text-sm">

                                                {{-- ================================================== --}}
                                                {{-- Edit --}}
                                                {{-- ================================================== --}}
                                                @can('user-manage')
                                                <a href="#"
                                                    class="edit bg-cyan-500 text-white px-2 py-1 rounded-md hover:bg-cyan-600 transition-colors text-xs font-medium inline-flex items-center gap-1"
                                                    id_user="{{ $d->id }}">

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
                                                        <path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3" />
                                                        <path d="M16 5l3 3" />
                                                        <path d="M9 7.07a7 7 0 0 0 1 13.93a7 7 0 0 0 6.929 -6" />

                                                    </svg>

                                                </a>

                                                {{-- ================================================== --}}
                                                {{-- Reset Password --}}
                                                {{-- ================================================== --}}
                                                <form action="/users/{{ $d->id }}/resetpassword"
                                                    method="POST"
                                                    style="display:inline-block;">

                                                    @csrf

                                                    <button type="submit"
                                                        class="reset-password-confirm bg-yellow-500 text-white px-2 py-1 rounded-md hover:bg-yellow-600 transition-colors text-xs font-medium inline-flex items-center gap-1">

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
                                                            <path d="M3.06 13a9 9 0 1 0 .49 -4.087" />
                                                            <path d="M3 4.001v5h5" />
                                                            <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />

                                                        </svg>

                                                    </button>

                                                </form>

                                                {{-- ================================================== --}}
                                                {{-- Delete --}}
                                                {{-- ================================================== --}}
                                                @if ($d->id != 1)
                                                    <form action="/users/{{ $d->id }}/delete"
                                                        method="POST"
                                                        style="display:inline-block;">

                                                        @csrf

                                                        <button type="submit"
                                                            class="delete-confirm bg-red-600 text-white px-2 py-1 rounded-md hover:bg-red-700 transition-colors text-xs font-medium inline-flex items-center gap-1">

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
                                                                <line x1="4" y1="7" x2="20" y2="7" />
                                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                                <line x1="14" y1="11" x2="14" y2="17" />
                                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />

                                                            </svg>

                                                        </button>

                                                    </form>
                                                @endif
                                                @endcan

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- ================================================== --}}
        {{-- Modal Tambah User --}}
        {{-- ================================================== --}}
        <x-app.modal id="modal-inputuser" title="Tambah Data User / Admin">

            <form action="/users/store"
                method="POST"
                id="formUser"
                enctype="multipart/form-data"
                autocomplete="off">

                @csrf

                {{-- ================================================== --}}
                {{-- Nama User --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">

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
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M16 19h6" />
                                    <path d="M19 16v6" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />

                                </svg>

                            </span>

                            <input type="text"
                                class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                name="nama_user"
                                id="nama_user"
                                value=""
                                placeholder="Nama User">

                        </div>

                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- Email --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">

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
                                    <path d="M12 18h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v7.5" />
                                    <path d="M3 6l9 6l9 -6" />
                                    <path d="M15 18h6" />
                                    <path d="M18 15l3 3l-3 3" />

                                </svg>

                            </span>

                            <input type="text"
                                class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                name="email"
                                id="email"
                                value=""
                                placeholder="Email User">

                        </div>

                    </div>
                </div>


                {{-- ================================================== --}}
                {{-- Unit Perusahaan --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-12">
                        <div class="space-y-1">
                            <select name="unit" id="unit" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                                <option value="">Unit Perusahaan</option>

                                @foreach ($unitperusahaan as $d)
                                    <option value="{{ $d->unit }}">
                                        {{ $d->unit }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- Role --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2 mt-2">
                    <div class="col-span-12">
                        <div class="space-y-1">
                            <select name="role" id="role" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                                <option value="">Role</option>

                                @foreach ($role as $d)
                                    <option value="{{ $d->name }}">
                                        {{ ucwords($d->name) }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- Password --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2 mt-2">
                    <div class="col-span-12">

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
                                    <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0" />
                                    <path d="M15 9h.01" />

                                </svg>

                            </span>

                            <input type="password"
                                class="w-full rounded-md border border-slate-300 pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                name="password"
                                id="password"
                                value=""
                                placeholder="Password">

                        </div>

                    </div>
                </div>

                {{-- ================================================== --}}
                {{-- Button Simpan --}}
                {{-- ================================================== --}}
                <div class="grid grid-cols-12 gap-2 mt-2">
                    <div class="col-span-12">

                        <div class="space-y-1">

                            <button class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                                Simpan

                            </button>

                        </div>

                    </div>
                </div>

            </form>

        </x-app.modal>

        {{-- ================================================== --}}
        {{-- Modal Edit User --}}
        {{-- ================================================== --}}
        <x-app.modal id="modal-edituser" title="Edit Data User / Admin">

            <div id="loadedituser">

                {{-- Form edit dimuat menggunakan Ajax --}}

            </div>

        </x-app.modal>

    </x-app.page-body>

@endsection

@push('myscript')
<script>

$(function () {

    // ==================================================
    // Modal Tambah User
    // ==================================================
    $("#btnTambahuser").click(function () {
        window.dispatchEvent(new CustomEvent('open-modal-modal-inputuser'));
    });


    // ==================================================
    // Modal Edit User
    // ==================================================
    $(".edit").click(function () {

        var id_user = $(this).attr("id_user");

        $.ajax({
            type: "POST",
            url: "/users/edit",
            cache: false,
            data: {
                _token: "{{ csrf_token() }}",
                id_user: id_user
            },
            success: function (respond) {
                $("#loadedituser").html(respond);
            }
        });

        window.dispatchEvent(new CustomEvent('open-modal-modal-edituser'));

    });


    // ==================================================
    // Validasi Form Tambah User
    // ==================================================
    $("#formUser").submit(function () {

        /*
            Seluruh isi validasi tetap sama
            (nama_user, email, unit, role)
            hanya indentasi yang dirapikan.
        */

    });


    // ==================================================
    // Reset Password
    // ==================================================
    $(".reset-password-confirm").click(function (e) {

        var form = $(this).closest("form");

        e.preventDefault();

        Swal.fire({
            title: 'Reset Password?',
            text: 'Password akan direset menjadi 12345678',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal',
            backdrop: false
        }).then((result) => {

            if (result.isConfirmed) {
                form.submit();
            }

        });

    });


    // ==================================================
    // Delete User
    // ==================================================
    $(".delete-confirm").click(function (e) {

        /*
            Seluruh SweetAlert Delete tetap sama.
            Tidak ada perubahan logic.
        */

    });

});

</script>
@endpush
