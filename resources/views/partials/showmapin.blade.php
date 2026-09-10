<style>
    #map { height: 250px; }
</style>

<div id="map"
     data-lokasi="{{ $presensi->lokasi_in }}"
     data-label="Lokasi Masuk - {{ $presensi->karyawan->nama_lengkap ?? '' }}"
     data-color="red">
</div>