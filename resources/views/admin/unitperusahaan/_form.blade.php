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
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8"/><path d="M13 7l0 .01"/><path d="M17 7l0 .01"/><path d="M17 11l0 .01"/><path d="M17 15l0 .01"/></svg>'
    />

    {{-- Perusahaan --}}
    <x-admin.input
        name="perusahaan"
        id="perusahaan"
        value="{{ $data?->perusahaan }}"
        placeholder="Nama Perusahaan"
        autocomplete="off"
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15"/><path d="M16 8h2c1 0 2 1 2 2v11"/><path d="M3 21h18"/><path d="M10 12v.01"/><path d="M10 16v.01"/><path d="M10 8v.01"/><path d="M7 12v.01"/><path d="M7 16v.01"/><path d="M7 8v.01"/><path d="M17 12v.01"/><path d="M17 16v.01"/></svg>'
    />

    {{-- Jam Masuk --}}
    <x-admin.input
        type="time"
        name="jam_masuk"
        id="jam_masuk"
        value="{{ $data?->jam_masuk }}"
        required
        icon='<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20.975 11.33a9 9 0 1 0 -5.717 9.06"/><path d="M12 7v5l2 2"/><path d="M19 22v.01"/><path d="M19 19a2.003 2.003 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483"/></svg>'
    />

    {{-- Submit --}}
    <div class="mt-2">
        <button
            type="submit"
            class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium w-full justify-center">
            @if($data)
                <i data-lucide="save" style="width:16px;height:16px;"></i>
                Perbarui Data
            @else
                <i data-lucide="plus" style="width:16px;height:16px;"></i>
                Simpan Data
            @endif
        </button>
    </div>

</form>
