@props(['karyawan' => null, 'unitperusahaan'])

<form action="{{ $karyawan ? '/karyawan/'.$karyawan->nik.'/update' : '/karyawan/store' }}"
      method="POST"
      id="formKaryawan"
      enctype="multipart/form-data">

    @csrf

    @if($karyawan)
        <input type="hidden" name="page" value="{{ request()->get('page', 1) }}">
    @endif

    {{-- NIK --}}
    <x-admin.input
        name="nik"
        id="nik"
        value="{{ $karyawan?->nik }}"
        placeholder="NIK"
        autocomplete="off"
        @if($karyawan) readonly class="bg-slate-50" @endif
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/><path d="M8 13h1v3h-1"/><path d="M12 13v3"/><path d="M15 13h1v3h-1"/></svg>'
    />

    {{-- Nama Lengkap --}}
    <x-admin.input
        name="nama_lengkap"
        id="nama_lengkap"
        value="{{ $karyawan?->nama_lengkap }}"
        placeholder="Nama Lengkap"
        autocomplete="off"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>'
    />

    {{-- Posisi --}}
    <x-admin.input
        name="posisi"
        id="posisi"
        value="{{ $karyawan?->posisi }}"
        placeholder="Posisi (contoh: Staff Accounting)"
        autocomplete="off"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1l0 -10"/><path d="M7 20l10 0"/><path d="M9 16l0 4"/><path d="M15 16l0 4"/><path d="M8 12l3 -3l2 2l3 -3"/></svg>'
    />

    {{-- Jabatan --}}
    <x-admin.select name="jabatan" id="jabatan" label="Jabatan <span class='text-red-500'>*</span>" placeholder="Pilih Jabatan" required>
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
            <option value="Manager" {{ ($karyawan?->role_approved ?? '') == 'Manager' ? 'selected' : '' }}>Manager</option>
            <option value="GM" {{ ($karyawan?->role_approved ?? '') == 'GM' ? 'selected' : '' }}>GM (General Manager)</option>
            <option value="Direktur" {{ ($karyawan?->role_approved ?? '') == 'Direktur' ? 'selected' : '' }}>Direktur</option>
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
            @foreach($atasanList as $a)
                <option value="{{ $a->nik }}" {{ ($karyawan?->atasan_nik ?? '') == $a->nik ? 'selected' : '' }}>{{ $a->nama_lengkap }} ({{ $a->jabatan }} - {{ $a->posisi }})</option>
            @endforeach
        </x-admin.select>
        <small class="text-slate-500">Atasan muncul sesuai Role Approved yang dipilih.</small>
    </div>

    {{-- Unit --}}
    <x-admin.select name="unit" id="unit" placeholder="Pilih Unit">
        @foreach ($unitperusahaan as $u)
            <option value="{{ $u->unit }}" {{ ($karyawan?->unit ?? '') == $u->unit ? 'selected' : '' }}>{{ $u->unit }}</option>
        @endforeach
    </x-admin.select>

    {{-- No. HP --}}
    <x-admin.input
        name="no_hp"
        id="no_hp"
        value="{{ $karyawan?->no_hp }}"
        placeholder="No. HP"
        autocomplete="off"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"/><path d="M15 6h6m-3 -3v6"/></svg>'
    />

    {{-- Upload Foto --}}
    <x-admin.input
        type="file"
        name="foto"
        label="Upload Foto"
    />

    @if($karyawan)
        <input type="hidden" name="foto_lama" value="{{ $karyawan->foto }}">
    @else
        <input type="hidden" name="foto_lama" value="">
    @endif

    {{-- Password --}}
    <div class="relative mb-2">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                @if($karyawan)
                    <path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0"/>
                    <path d="M15 9h.01"/>
                @else
                    <path d="M12 3a2 2 0 0 0 -2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2h-14a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2h-1"/>
                    <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                @endif
            </svg>
        </span>
        <input
            type="password"
            name="password"
            id="password"
            class="w-full rounded-md border border-slate-300 pl-9 pr-10 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-colors"
            placeholder="{{ $karyawan ? 'Kosongkan jika tidak diubah' : 'Password' }}"
            @if(!$karyawan) required @endif>
        <button type="button"
            onclick="togglePassword('password', this)"
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

    {{-- Submit --}}
    <div class="mt-2">
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">
            @if($karyawan)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13.5 16h-9.5a1 1 0 0 1 -1 -1v-10a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v7.5"/><path d="M7 20h5"/><path d="M9 16v4"/><path d="M19 16v6"/><path d="M22 19l-3 3l-3 -3"/></svg>
                Perbarui Data
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2v1a2 2 0 0 0 2 2h-14a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2v-1a2 2 0 0 0 -2 -2h-1"/><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/></svg>
                Simpan Data
            @endif
        </button>
    </div>

</form>
