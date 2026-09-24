<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#7A5234">
    <title>WAG - Presensi Digital</title>
    <meta name="description" content="Aplikasi Presensi Digital untuk Karyawan WAG berbasis web mobile">
    <meta name="keywords" content="presensi digital, absensi karyawan, aplikasi absensi, WAG">

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/login/logo_aplikasi.png') }}">
    <link rel="manifest" href="/manifest.json">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="loginPage()" class="h-[100svh] overflow-hidden bg-white antialiased">
    <div id="appCapsule" class="h-[100svh] overflow-hidden">
        <main class="flex h-full flex-col md:flex-row">

            {{-- Mega Mendung --}}
            <section
                class="relative h-[38svh] min-h-[250px] shrink-0 overflow-hidden bg-[#0a192f] md:h-full md:min-h-0 md:w-1/2">
                <img src="{{ asset('assets/img/login/mega-mendung-brown.webp') }}" alt=""
                    class="absolute inset-0 h-full w-full object-cover object-center">

                <div class="absolute inset-0 bg-[#0a192f]/10"></div>

                {{-- Branding --}}
                <div class="absolute inset-0 z-20 flex items-center justify-center">
                    <div class="flex flex-col items-center text-center">
                        <img src="{{ asset('assets/img/login/logo_aplikasi.png') }}" alt="WAG Presensi Digital"
                            class="w-[120px] rounded-[8px] drop-shadow-[0_8px_20px_rgba(0,0,0,0.18)] sm:w-[165px] md:w-[190px]">
                    </div>
                </div>

                {{-- Mobile Wave --}}
                <div class="absolute bottom-0 left-0 z-30 h-20 w-full md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"
                        class="block h-full w-full">
                        <path fill="#ffffff"
                            d="M0,64L48,53.3C96,43,192,21,288,37.3C384,53,480,107,576,128C672,149,768,139,864,122.7C960,107,1056,85,1152,90.7C1248,96,1344,128,1392,144L1440,160L1440,320L0,320Z">
                        </path>
                    </svg>
                </div>
            </section>

            {{-- Login --}}
            <section class="relative z-40 -mt-[2px] flex min-h-0 flex-1 items-center justify-center bg-white px-6 py-6 md:mt-0 md:w-1/2 md:px-12 lg:px-20">
                <div class="w-full max-w-[460px]">

                    <div class="mb-6 md:mb-8">
                        <h1
                            class="mb-1.5 text-[25px] font-bold leading-tight tracking-[-0.025em] text-[#172033] md:text-[34px]">
                            Selamat Datang
                        </h1>
                        <p class="text-[12px] leading-relaxed text-[#7b8492] md:text-[14px]">
                            Silakan masuk dengan akunmu untuk melanjutkan ke aplikasi.
                        </p>
                    </div>

                    @php
                        $messagewarning = Session::get('warning');
                    @endphp

                    @if (Session::get('warning'))
                        <div class="mb-4 border border-[#ec4433] bg-[#ec4433]/5 px-3 py-2 text-[12px] text-[#d63b2d]">
                            {{ $messagewarning }}
                        </div>
                    @endif

                    <form action="/proseslogin" method="POST" autocomplete="off">
                        @csrf

                        <div class="mb-4">
                            <label for="nik"
                                class="mb-1.5 block text-[11px] font-semibold text-[#344054] md:text-[12px]">
                                NIK
                            </label>
                            <input type="text" name="nik" id="nik" placeholder="Masukkan NIK"
                                autocomplete="username"
                                class="h-11 w-full rounded-[5px] border border-[#d9dde3] bg-white px-3 text-[13px] text-[#172033] outline-none transition placeholder:text-[#a2aab6] focus:border-coklat focus:ring-2 focus:ring-coklat/10 md:h-12 md:text-[14px]">
                        </div>

                        <div class="mb-4">
                            <label for="password"
                                class="mb-1.5 block text-[11px] font-semibold text-[#344054] md:text-[12px]">
                                Password
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                                    placeholder="Masukkan password" autocomplete="current-password"
                                    class="h-11 w-full rounded-[5px] border border-[#d9dde3] bg-white px-3 pr-11 text-[13px] text-[#172033] outline-none transition placeholder:text-[#a2aab6] focus:border-coklat focus:ring-2 focus:ring-coklat/10 md:h-12 md:text-[14px]">
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-2.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center border-0 bg-transparent p-0 text-[#8b95a5] hover:text-coklat"
                                    aria-label="Tampilkan atau sembunyikan password">
                                    <i x-show="!showPassword" data-lucide="eye" class="h-[18px] w-[18px]"></i>
                                    <i x-show="showPassword" data-lucide="eye-off" class="h-[18px] w-[18px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6 flex justify-end md:mb-7">
                            <a href="#" @click.prevent="showForgotPassword()"
                                class="text-[11px] font-medium text-coklat hover:text-coklat-dark md:text-[12px]">
                                Lupa Password?
                            </a>
                        </div>

                        <button type="submit"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-[5px] border-0 bg-coklat px-5 text-[13px] font-semibold text-white transition hover:bg-coklat-dark active:translate-y-px md:h-12 md:text-[14px]">
                            Masuk
                            <i data-lucide="arrow-right" class="h-4 w-4"></i>
                        </button>
                    </form>

                    <div class="mt-7 hidden items-center gap-3 md:flex">
                        <span class="h-px flex-1 bg-[#e2e5e8]"></span>
                        <span class="text-[10px] text-[#98a0ab]">WAG Presensi Digital</span>
                        <span class="h-px flex-1 bg-[#e2e5e8]"></span>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        function loginPage() {
            return {
                showPassword: false,
                showForgotPassword() {
                    Swal.fire({
                        title: 'Lupa Password?',
                        text: 'Silahkan hubungi admin!',
                        icon: 'warning',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#9c6b43'
                    });
                }
            }
        }
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker Registered');
                    })
                    .catch(function(error) {
                        console.log('Service Worker Failed', error);
                    });
            });
        }
    </script>
</body>

</html>
