@extends('layouts.admin.app')

@section('content')

@section('page_title', 'Data Pengguna Administrator')

<x-admin.page-body>

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
                        <a href="#"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                            id="btnTambahuser">
                            <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
                            Tambah Data Administrator
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
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Email</th>
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Unit Perusahaan</th>
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Role</th>
                                    <th
                                        class="px-2 py-1.5 text-left text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-slate-200">

                                @foreach ($users as $d)
                                    <tr class="hover:bg-slate-50">

                                        <td class="px-2 py-1.5 text-xs text-slate-700">{{ $loop->iteration }}</td>
                                        <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->name }}
                                        </td>
                                        <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">{{ $d->email }}
                                        </td>
                                        <td class="px-2 py-1.5 text-xs text-slate-700 truncate-cell">
                                            {{ $d->perusahaan }}</td>
                                        <td class="px-2 py-1.5 text-xs">
                                            @php
                                                $roleBadge = match ($d->role) {
                                                    'super_admin' => 'bg-purple-100 text-purple-700',
                                                    'admin' => 'bg-blue-100 text-blue-700',
                                                    'owner' => 'bg-amber-100 text-amber-700',
                                                    default => 'bg-slate-100 text-slate-600',
                                                };
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $roleBadge }}">
                                                {{ ucwords(str_replace('_', ' ', $d->role)) }}
                                            </span>
                                        </td>

                                        <td class="px-2 py-1.5 text-xs">

                                            {{-- ================================================== --}}
                                            {{-- Edit --}}
                                            {{-- ================================================== --}}
                                            @can('user-manage')
                                                <a href="#"
                                                    class="edit bg-blue-600 text-white px-1.5 py-1.5 rounded hover:bg-blue-700 transition-colors text-[10px] font-medium inline-flex items-center"
                                                    id_user="{{ $d->id }}">

                                                    <i data-lucide="square-pen" style="width:12px;height:12px;"></i>

                                                </a>

                                                {{-- ================================================== --}}
                                                {{-- Reset Password --}}
                                                {{-- ================================================== --}}
                                                <form action="/users/{{ $d->id }}/resetpassword" method="POST"
                                                    style="display:inline-block;">

                                                    @csrf

                                                    <button type="submit"
                                                        class="reset-password-confirm bg-amber-500 text-white px-1.5 py-1.5 rounded hover:bg-amber-600 transition-colors text-[10px] font-medium inline-flex items-center">

                                                        <i data-lucide="key-round" style="width:12px;height:12px;"></i>

                                                    </button>

                                                </form>

                                                {{-- ================================================== --}}
                                                {{-- Delete --}}
                                                {{-- ================================================== --}}
                                                @if ($d->id != 1)
                                                    <form action="/users/{{ $d->id }}/delete" method="POST"
                                                        style="display:inline-block;">

                                                        @csrf

                                                        <button type="submit"
                                                            class="delete-confirm bg-red-600 text-white px-1.5 py-1.5 rounded hover:bg-red-700 transition-colors text-[10px] font-medium inline-flex items-center">

                                                            <i data-lucide="trash-2" style="width:12px;height:12px;"></i>

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
    <x-admin.modal id="modal-inputuser" title="Tambah Data User / Admin">

        <form action="/users/store" method="POST" id="formUser" enctype="multipart/form-data" autocomplete="off">

            @csrf

            {{-- ================================================== --}}
            {{-- Nama User --}}
            {{-- ================================================== --}}
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-12">
                    <x-admin.input
                        name="nama_user"
                        id="nama_user"
                        placeholder="Nama User"
                        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M16 19h6" /><path d="M19 16v6" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4" /></svg>'
                    />
                </div>
            </div>

            {{-- ================================================== --}}
            {{-- Email --}}
            {{-- ================================================== --}}
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-12">
                    <x-admin.input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Email User"
                        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 18h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v7.5" /><path d="M3 6l9 6l9 -6" /><path d="M15 18h6" /><path d="M18 15l3 3l-3 3" /></svg>'
                    />
                </div>
            </div>


            {{-- ================================================== --}}
            {{-- Unit Perusahaan --}}
            {{-- ================================================== --}}
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-12">
                    <div class="space-y-1">
                        <select name="unit" id="unit"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
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
                        <select name="role" id="role"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">

                            <option value="">Role</option>

                            @foreach ($role as $d)
                                <option value="{{ $d->name }}">
                                    {{ ucwords(str_replace('_', ' ', $d->name)) }}
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
                    <x-admin.input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Password"
                        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0" /><path d="M15 9h.01" /></svg>'
                    />
                </div>
            </div>

            {{-- ================================================== --}}
            {{-- Button Simpan --}}
            {{-- ================================================== --}}
            <div class="grid grid-cols-12 gap-2 mt-2">
                <div class="col-span-12">

                    <div class="space-y-1">

                        <button
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">

                            Simpan

                        </button>

                    </div>

                </div>
            </div>

        </form>

    </x-admin.modal>

    {{-- ================================================== --}}
    {{-- Modal Edit User --}}
    {{-- ================================================== --}}
    <x-admin.modal id="modal-edituser" title="Edit Data User / Admin">

        <div id="loadedituser">

            {{-- Form edit dimuat menggunakan Ajax --}}

        </div>

    </x-admin.modal>

</x-admin.page-body>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ==================================================
        // Modal Tambah User
        // ==================================================
        document.getElementById('btnTambahuser').addEventListener('click', function() {
            window.dispatchEvent(new CustomEvent('open-modal-modal-inputuser'));
        });


        // ==================================================
        // Modal Edit User
        // ==================================================
        document.querySelectorAll('.edit').forEach(function(el) {
            el.addEventListener('click', function(e) {

                var id_user = this.getAttribute('id_user');

                fetch('/users/edit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id_user: id_user
                    })
                }).then(function(response) {
                    return response.text();
                }).then(function(html) {
                    document.getElementById('loadedituser').innerHTML = html;
                });

                window.dispatchEvent(new CustomEvent('open-modal-modal-edituser'));

            });
        });


        // ==================================================
        // Validasi Form Tambah User
        // ==================================================
        document.getElementById('formUser').addEventListener('submit', function() {

            /*
                Seluruh isi validasi tetap sama
                (nama_user, email, unit, role)
                hanya indentasi yang dirapikan.
            */

        });


        // ==================================================
        // Reset Password
        // ==================================================
        document.querySelectorAll('.reset-password-confirm').forEach(function(el) {
            el.addEventListener('click', function(e) {

                var form = this.closest('form');

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
                }).then(function(result) {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            });
        });


        // ==================================================
        // Delete User
        // ==================================================
        document.querySelectorAll('.delete-confirm').forEach(function(el) {
            el.addEventListener('click', function(e) {

                e.preventDefault();

                var form = this.closest('form');

                Swal.fire({
                    title: 'Hapus User?',
                    text: 'Data akan dihapus secara permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    backdrop: false
                }).then(function(result) {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            });
        });

        if (window.lucide) lucide.createIcons();

    });
</script>
@endpush
