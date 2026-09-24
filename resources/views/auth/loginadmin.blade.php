<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#7A5234">
    <title>Dashboard Administrator WAG - Presensi Digital</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}">

    @vite(['resources/css/app.css'])
</head>

<body class="bg-white antialiased">
    <main class="flex min-h-[100svh] flex-col md:h-[100svh] md:flex-row">

        {{-- Mega Mendung --}}
        <section
            class="relative h-[32svh] min-h-[220px] shrink-0 overflow-hidden bg-[#0a192f] md:h-full md:min-h-0 md:w-1/2">
            <img src="{{ asset('assets/img/login/mega-mendung-brown.webp') }}" alt=""
                class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-[#0a192f]/10"></div>

            {{-- Branding --}}
            <div class="absolute inset-0 z-20 flex items-center justify-center ">
                <a href="/panel" class="flex flex-col items-center rounded-lg bg-black/30 p-1 text-center backdrop-blur-xs md:p-5">
                    <img src="{{ asset('assets/img/login/logo_aplikasi_admin.png') }}" alt="WAG Presensi Digital"
                        class="w-[120px] brightness-0 invert rounded-[8px] drop-shadow-[0_8px_20px_rgba(0,0,0,0.18)] sm:w-[165px] md:w-[210px]">
                </a>
            </div>

            {{-- Mobile Wave --}}
            <div class="absolute bottom-0 left-0 z-30 h-16 w-full md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"
                    class="block h-full w-full">
                    <path fill="#ffffff"
                        d="M0,64L48,53.3C96,43,192,21,288,37.3C384,53,480,107,576,128C672,149,768,139,864,122.7C960,107,1056,85,1152,90.7C1248,96,1344,128,1392,144L1440,160L1440,320L0,320Z">
                    </path>
                </svg>
            </div>
        </section>

        {{-- Login --}}
        <section
            class="relative z-40 -mt-[2px] flex flex-1 items-center justify-center bg-white px-6 py-8 md:mt-0 md:w-1/2 md:px-12 lg:px-20">
            <div class="w-full max-w-[460px]">

                <div class="mb-6 md:mb-8">
                    <span
                        class="mb-3 inline-block rounded-md bg-coklat/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-coklat">
                        Administrator
                    </span>
                    <h1
                        class="mb-1.5 text-[25px] font-bold leading-tight tracking-[-0.025em] text-[#172033] md:text-[34px]">
                        Selamat Datang
                    </h1>
                    <p class="text-[12px] leading-relaxed text-[#7b8492] md:text-[14px]">
                        Masuk dengan akun administrator untuk mengelola presensi.
                    </p>
                </div>

                {{-- Alert --}}
                @if (Session::get('danger'))
                    <div
                        class="mb-4 rounded-[5px] border border-[#ec4433] bg-[#ec4433]/5 px-3 py-2 text-[12px] text-[#d63b2d]">
                        {{ Session::get('danger') }}
                    </div>
                @endif

                <form action="/prosesloginadmin" method="POST" autocomplete="off">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email"
                            class="mb-1.5 block text-[11px] font-semibold text-[#344054] md:text-[12px]">Email</label>
                        <input type="email" name="email" id="email" placeholder="Masukkan email"
                            autocomplete="username" value="{{ old('email') }}"
                            class="h-11 w-full rounded-[5px] border border-[#d9dde3] bg-white px-3 text-[13px] text-[#172033] outline-none transition placeholder:text-[#a2aab6] focus:border-coklat focus:ring-2 focus:ring-coklat/10 md:h-12 md:text-[14px]">
                    </div>

                    {{-- Password --}}
                    <div class="mb-6 md:mb-7">
                        <label for="password"
                            class="mb-1.5 block text-[11px] font-semibold text-[#344054] md:text-[12px]">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="Masukkan password"
                                autocomplete="current-password"
                                class="h-11 w-full rounded-[5px] border border-[#d9dde3] bg-white px-3 pr-11 text-[13px] text-[#172033] outline-none transition placeholder:text-[#a2aab6] focus:border-coklat focus:ring-2 focus:ring-coklat/10 md:h-12 md:text-[14px]">
                            <button type="button" id="togglePassword"
                                class="absolute right-2.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center border-0 bg-transparent p-0 text-[#8b95a5] hover:text-coklat"
                                aria-label="Tampilkan atau sembunyikan password">
                                <svg class="eye-open h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="eye-closed hidden h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                    <path
                                        d="M10.73 5.08A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17.7 17.7 0 0 1-2.16 3.19" />
                                    <path d="M6.61 6.61A17.5 17.5 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.39-1.61" />
                                    <path d="m2 2 20 20" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Button --}}
                    <button type="submit"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-[5px] border-0 bg-coklat px-5 text-[13px] font-semibold text-white transition hover:bg-coklat-dark active:translate-y-px md:h-12 md:text-[14px]">
                        Masuk
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
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

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.querySelector('.eye-open').classList.toggle('hidden', show);
            this.querySelector('.eye-closed').classList.toggle('hidden', !show);
        });
    </script>
</body>

</html>
