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

    {{-- Submit --}}
    <div class="mt-2">
        @if($data)
            <x-admin.button variant="primary" icon="save" type="submit" block>Perbarui Data</x-admin.button>
        @else
            <x-admin.button variant="primary" icon="plus" type="submit" block>Simpan Data</x-admin.button>
        @endif
    </div>

</form>
