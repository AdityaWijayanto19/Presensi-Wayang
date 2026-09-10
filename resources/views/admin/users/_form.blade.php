@props(['user' => null])

<form action="{{ $user ? '/users/'.$user->id.'/update' : '/users/store' }}"
    method="POST"
    id="formUser"
    autocomplete="off">

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
                value="{{ $user?->name }}"
                icon="user-plus"
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
                value="{{ $user?->email }}"
                icon="mail"
            />
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Unit Perusahaan --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">
            <x-admin.select name="unit" id="unit" placeholder="Unit Perusahaan">
                @foreach ($unitperusahaan as $d)
                    <option value="{{ $d->unit }}" {{ $user?->unit == $d->unit ? 'selected' : '' }}>{{ $d->unit }}</option>
                @endforeach
            </x-admin.select>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Role --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">
            <x-admin.select name="role" id="role" placeholder="Role">
                @foreach ($role as $d)
                    <option value="{{ $d->name }}" {{ $user?->roles->pluck('name')->first() === $d->name ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $d->name)) }}</option>
                @endforeach
            </x-admin.select>
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Password --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2">
        <div class="col-span-12">
            <x-admin.input
                type="password"
                name="password"
                id="password"
                placeholder="{{ $user ? 'Kosongkan jika tidak diubah' : 'Password' }}"
                icon="lock"
            />
        </div>
    </div>

    {{-- ================================================== --}}
    {{-- Button --}}
    {{-- ================================================== --}}
    <div class="grid grid-cols-12 gap-2 mt-2">
        <div class="col-span-12">
            @if($user)
                <x-admin.button variant="primary" icon="save" type="submit" block>Perbarui Data</x-admin.button>
            @else
                <x-admin.button variant="primary" icon="plus" type="submit" block>Simpan</x-admin.button>
            @endif
        </div>
    </div>

</form>
