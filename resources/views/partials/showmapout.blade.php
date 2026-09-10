<style>
    #map { height: 250px; }
</style>

<div id="map"
     data-lokasi="{{ $presensi->lokasi_out }}"
     data-label="Lokasi Pulang - {{ $presensi->karyawan->nama_lengkap ?? '' }}"
     data-color="blue">
</div>