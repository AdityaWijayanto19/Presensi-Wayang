@props(['data' => null])

<form action="{{ $data ? '/unitperusahaan/'.$data->id.'/update' : '/unitperusahaan/store' }}"
      method="POST"
      id="formUnitperusahaan"
      enctype="multipart/form-data">

    @csrf

    {{-- Unit --}}
    <x-admin.input
        name="unit"
        id="unit"
        value="{{ $data?->unit }}"
        placeholder="Nama Unit"
        autocomplete="off"
        icon="building"
    />

    {{-- Perusahaan --}}
    <x-admin.input
        name="perusahaan"
        id="perusahaan"
        value="{{ $data?->perusahaan }}"
        placeholder="Nama Perusahaan"
        autocomplete="off"
        icon="building-2"
    />

    {{-- Jam Masuk --}}
    <x-admin.input
        type="time"
        name="jam_masuk"
        id="jam_masuk"
        value="{{ $data?->jam_masuk }}"
        required
        icon="clock"
    />

    {{-- Radius Presensi --}}
    <x-admin.input
        type="number"
        name="radius_meter"
        id="radius_meter"
        value="{{ $data?->radius_meter ?? 100 }}"
        placeholder="Radius presensi (meter)"
        min="10"
        max="10000"
        icon="circle"
    />

    {{-- Lokasi Kantor (multi titik) --}}
    <div class="mb-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">
            Lokasi Kantor <span class="text-slate-400 font-normal">(boleh lebih dari satu)</span>
        </label>

        <div id="lokasi-rows" class="space-y-2"></div>

        <button type="button" id="btnTambahLokasi" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Tambah Lokasi
        </button>

        <p class="mt-1 text-[11px] text-slate-500">
            Kosongkan jika unit belum memiliki titik lokasi (validasi radius dilewati).
        </p>
    </div>

    <template id="tpl-lokasi-row">
        <div class="lokasi-row rounded-md border border-slate-200 p-2 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium text-slate-500">Titik Lokasi</span>
                <button type="button" class="hapus-lokasi text-red-500 hover:text-red-700" title="Hapus lokasi">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </div>

            <input type="text" class="lok-nama w-full rounded-md border border-slate-300 text-sm px-3 py-2"
                   placeholder="Nama lokasi (mis. Kantor Pusat)" maxlength="100">

            <div class="grid grid-cols-2 gap-2">
                <input type="number" step="any" class="lok-lat w-full rounded-md border border-slate-300 text-sm px-3 py-2"
                       placeholder="Latitude">
                <input type="number" step="any" class="lok-lng w-full rounded-md border border-slate-300 text-sm px-3 py-2"
                       placeholder="Longitude">
            </div>
        </div>
    </template>

    {{-- Submit --}}
    <div class="mt-2">
        @if($data)
            <x-admin.button variant="primary" icon="save" type="submit" block>Perbarui Data</x-admin.button>
        @else
            <x-admin.button variant="primary" icon="plus" type="submit" block>Simpan Data</x-admin.button>
        @endif
    </div>

</form>
