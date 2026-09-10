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
    {{-- Modal Form User (Create / Edit) --}}
    {{-- ================================================== --}}
    <template id="formTemplate">
        @include('admin.users._form')
    </template>

    <x-admin.modal id="modal-userform" title="Form User / Admin">
        <div id="formContainer"></div>
    </x-admin.modal>

</x-admin.page-body>

@endsection

@php
    $usersJson = $users->map(fn($u) => [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'unit' => $u->unit,
        'role' => $u->role,
    ])->toJson();
@endphp

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        var usersData = {!! $usersJson !!};
        var formTpl = document.getElementById('formTemplate');
        var formContainer = document.getElementById('formContainer');

        function openModal() {
            window.dispatchEvent(new CustomEvent('open-modal-modal-userform'));
        }

        // ==================================================
        // Tambah User — clone form kosong
        // ==================================================
        document.getElementById('btnTambahuser').addEventListener('click', function() {
            formContainer.innerHTML = '';
            formContainer.appendChild(formTpl.content.cloneNode(true));
            openModal();
        });

        // ==================================================
        // Edit User — clone + populate dari JSON
        // ==================================================
        document.querySelectorAll('.edit').forEach(function(el) {
            el.addEventListener('click', function(e) {
                var id = this.getAttribute('id_user');
                var user = usersData.find(function(u) { return u.id == id; });
                if (!user) return;

                var clone = formTpl.content.cloneNode(true);
                var form = clone.querySelector('form');

                form.action = '/users/' + user.id + '/update';
                form.querySelector('[name="nama_user"]').value = user.name;
                form.querySelector('[name="email"]').value = user.email;
                form.querySelector('[name="unit"]').value = user.unit;
                form.querySelector('[name="role"]').value = user.role;
                form.querySelector('[name="password"]').placeholder = 'Kosongkan jika tidak diubah';

                var btn = form.querySelector('button');
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12" /><path d="M13 8l3 3l-3 3" /><path d="M16 11h-8" /></svg> Perbarui Data!';

                formContainer.innerHTML = '';
                formContainer.appendChild(clone);
                openModal();
            });
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
