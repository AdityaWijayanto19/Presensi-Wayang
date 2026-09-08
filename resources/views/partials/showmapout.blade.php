{{-- ================================================== --}}
{{-- Map Style --}}
{{-- ================================================== --}}
<style>
    #map {
        height: 250px;
    }
</style>

{{-- ================================================== --}}
{{-- Map Container --}}
{{-- ================================================== --}}
<div id="map"></div>

{{-- ================================================== --}}
{{-- Leaflet Map --}}
{{-- ================================================== --}}
<script>

    var lokasi = "{{ $presensi->lokasi_out }}";
    var lok = lokasi.split(",");

    var latitude = lok[0];
    var longitude = lok[1];

    var map = L.map('map').setView([latitude, longitude], 16);

    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);

    var marker = L.marker([latitude, longitude]).addTo(map);

    marker.bindPopup("Lokasi Pulang - {{ $presensi->nama_lengkap }}");

    var circle = L.circle([latitude, longitude], {
        color: 'blue',
        fillColor: '#0d6efd',
        fillOpacity: 0.5,
        radius: 15
    }).addTo(map);

    setTimeout(function () {

        map.invalidateSize();
        marker.openPopup();

    }, 300);

</script>