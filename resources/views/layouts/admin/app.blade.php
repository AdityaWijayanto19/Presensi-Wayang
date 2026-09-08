<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>WAG Presensi Digital - Administrator</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    <div class="flex min-h-screen">

        @include('layouts.admin.sidebar')

        <div class="flex-1 flex flex-col lg:pl-60">

            @include('layouts.admin.header')

            <main class="flex-1">
                @yield('content')
            </main>

            @include('layouts.admin.footer')

        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $("#logout-admin").click(function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                Swal.fire({
                    title: 'Yakin ingin logout?',
                    text: 'Anda akan keluar dari sistem.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal',
                    backdrop: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

    @if (Session::get('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ Session::get("success") }}',
                backdrop: false
            });
        </script>
    @endif

    @if (Session::get('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: '{{ Session::get("warning") }}',
                backdrop: false
            });
        </script>
    @endif

    @if (Session::get('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '{{ Session::get("error") }}',
                backdrop: false
            });
        </script>
    @endif

    <script>
    (function(){
        function pollAdminBadge(){
            fetch('/api/realtime/admin', { credentials: 'same-origin' })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    var badgeEl = document.getElementById('adminWfhBadge');
                    var total = (data.pending_wfh || 0) + (data.pending_laporan || 0);
                    if(badgeEl){
                        if(total > 0){
                            badgeEl.textContent = total;
                            badgeEl.style.display = 'inline-flex';
                        } else {
                            badgeEl.style.display = 'none';
                        }
                    }
                }).catch(function(){});
        }
        pollAdminBadge();
        setInterval(pollAdminBadge, 5000);
    })();
    </script>

    @stack('myscript')

</body>

</html>
