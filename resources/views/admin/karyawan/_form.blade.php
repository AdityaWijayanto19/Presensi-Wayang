@props(['karyawan' => null, 'unitperusahaan'])

<form action="{{ $karyawan ? '/karyawan/' . $karyawan->nik . '/update' : '/karyawan/store' }}" method="POST" id="formKaryawan"
    enctype="multipart/form-data">

    @csrf

    @if ($karyawan)
        <input type="hidden" name="page" value="{{ request()->get('page', 1) }}">
    @endif

    {{-- NIK --}}
    <x-admin.input name="nik" id="nik" value="{{ $karyawan?->nik }}" placeholder="NIK" autocomplete="off"
        @if ($karyawan) readonly class="bg-slate-50" @endif
        icon="file-text" />

    {{-- Nama Lengkap --}}
    <x-admin.input name="nama_lengkap" id="nama_lengkap" value="{{ $karyawan?->nama_lengkap }}"
        placeholder="Nama Lengkap" autocomplete="off"
        icon="user" />

    {{-- Posisi --}}
    <x-admin.input name="posisi" id="posisi" value="{{ $karyawan?->posisi }}"
        placeholder="Posisi (contoh: Staff Accounting)" autocomplete="off"
        icon="briefcase" />

    {{-- Jabatan --}}
    <x-admin.select name="jabatan" id="jabatan" label="Jabatan <span class='text-red-500'>*</span>"
        placeholder="Pilih Jabatan" required>
        <option value="Intern" {{ ($karyawan?->jabatan ?? '') == 'Intern' ? 'selected' : '' }}>Intern</option>
        <option value="Staff" {{ ($karyawan?->jabatan ?? '') == 'Staff' ? 'selected' : '' }}>Staff</option>
        <option value="SPV" {{ ($karyawan?->jabatan ?? '') == 'SPV' ? 'selected' : '' }}>SPV (Supervisor)</option>
        <option value="Manager" {{ ($karyawan?->jabatan ?? '') == 'Manager' ? 'selected' : '' }}>Manager</option>
        <option value="GM" {{ ($karyawan?->jabatan ?? '') == 'GM' ? 'selected' : '' }}>GM (General Manager)</option>
        <option value="Direktur" {{ ($karyawan?->jabatan ?? '') == 'Direktur' ? 'selected' : '' }}>Direktur</option>
    </x-admin.select>

    {{-- Role Approved --}}
    @php
        $showRoleApproved = $karyawan && !empty($karyawan->jabatan) && $karyawan->jabatan !== 'Direktur';
    @endphp
    <div id="role-approved-wrapper" style="{{ $showRoleApproved ? '' : 'display:none;' }}">
        <x-admin.select name="role_approved" id="role_approved" label="Role Approved" placeholder="Pilih Role Approved">
            <option value="Staff" {{ ($karyawan?->role_approved ?? '') == 'Staff' ? 'selected' : '' }}>Staff</option>
            <option value="Manager" {{ ($karyawan?->role_approved ?? '') == 'Manager' ? 'selected' : '' }}>Manager
            </option>
            <option value="GM" {{ ($karyawan?->role_approved ?? '') == 'GM' ? 'selected' : '' }}>GM (General
                Manager)</option>
            <option value="Direktur" {{ ($karyawan?->role_approved ?? '') == 'Direktur' ? 'selected' : '' }}>Direktur
            </option>
        </x-admin.select>
        <small class="text-slate-500">Role yang berwenang menyetujui WFH/Lembur karyawan ini.</small>
    </div>

    {{-- Atasan --}}
    @php
        $showAtasan = $karyawan && !empty($karyawan->role_approved);
        $atasanList = collect();
        if ($showAtasan) {
            $atasanMap = ['Staff' => 'Manager', 'Manager' => 'GM', 'GM' => 'Direktur', 'Direktur' => null];
            $targetPosisi = $atasanMap[$karyawan->role_approved] ?? null;
            if ($targetPosisi) {
                $atasanList = DB::table('karyawans')
                    ->where('jabatan', $targetPosisi)
                    ->where('nik', '!=', $karyawan->nik)
                    ->get();
            }
        }
    @endphp
    <div id="atasan-wrapper" style="{{ $showAtasan ? '' : 'display:none;' }}">
        <x-admin.select name="atasan_nik" id="atasan_nik" placeholder="Pilih Atasan">
            @foreach ($atasanList as $a)
                <option value="{{ $a->nik }}" {{ ($karyawan?->atasan_nik ?? '') == $a->nik ? 'selected' : '' }}>
                    {{ $a->nama_lengkap }} ({{ $a->jabatan }} - {{ $a->posisi }})</option>
            @endforeach
        </x-admin.select>
        <small class="text-slate-500">Atasan muncul sesuai Role Approved yang dipilih.</small>
    </div>

    {{-- Unit --}}
    <x-admin.select name="unit" id="unit" searchable placeholder="Pilih Unit">
        @foreach ($unitperusahaan as $u)
            <option value="{{ $u->unit }}" {{ ($karyawan?->unit ?? '') == $u->unit ? 'selected' : '' }}>
                {{ $u->unit }}</option>
        @endforeach
    </x-admin.select>

    {{-- No. HP --}}
    <x-admin.input name="no_hp" id="no_hp" value="{{ $karyawan?->no_hp }}" placeholder="No. HP"
        autocomplete="off"
        icon="phone" />

    {{-- Upload Foto --}}
    <x-admin.image-crop name="foto" id="foto" currentImage="{{ $karyawan?->foto }}" />

    @if ($karyawan)
        <input type="hidden" name="foto_lama" value="{{ $karyawan->foto }}">
    @else
        <input type="hidden" name="foto_lama" value="">
    @endif

    {{-- Password --}}
    <div class="relative mb-2">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
        </span>
        <input type="password" name="password" id="password"
            class="w-full rounded-md border border-slate-300 pl-9 pr-10 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
            placeholder="{{ $karyawan ? 'Kosongkan jika tidak diubah' : 'Password' }}"
            @if (!$karyawan) required @endif>
        <button type="button" onclick="togglePassword('password', this)"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
            tabindex="-1">
            <span class="eye-open"><i data-lucide="eye" style="width:18px;height:18px;"></i></span>
            <span class="eye-closed" style="display:none;"><i data-lucide="eye-off" style="width:18px;height:18px;"></i></span>
        </button>
    </div>

    {{-- Submit --}}
    <div class="mt-2">
        @if ($karyawan)
            <x-admin.button variant="primary" icon="save" type="submit" block>Perbarui Data</x-admin.button>
        @else
            <x-admin.button variant="primary" icon="plus" type="submit" block>Simpan Data</x-admin.button>
        @endif
    </div>

</form>
