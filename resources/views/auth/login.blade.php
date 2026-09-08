<!doctype html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />

    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#7A5234">

    <title>WAG - Presensi Digital</title>

    <meta name="description" content="Aplikasi Presensi Digital untuk Karyawan WAG berbasis web mobile">
    <meta name="keywords" content="presensi digital, absensi karyawan, aplikasi absensi, WAG" />

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/login/logo_aplikasi.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    colors: {
                        coklat: '#7a5234',
                        'coklat-dark': '#5e3e27',
                    }
                }
            }
        }
    </script>

    <style>
        @import url("https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap");

        /* CSS RESET */
        *, *::before, *::after { box-sizing: border-box; }
        body, h1, h2, h3, h4, h5, h6, p, ul, ol, figure, blockquote, dl, dd {
            margin: 0;
        }
        ul, ol { padding: 0; list-style: none; }
        img, video, canvas, svg { display: block; max-width: 100%; }
        img { height: auto; }
        a { color: inherit; text-decoration: none; }
        input, button, textarea, select { font: inherit; }
        table { border-collapse: collapse; border-spacing: 0; }

        body {
            font-family: "Inter", sans-serif;
            font-size: 15px;
            line-height: 1.55rem;
            letter-spacing: -0.015rem;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overscroll-behavior-y: none;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar { width: 0; }
        button { outline: 0 !important; }
        i[data-lucide] { width: 22px; height: 22px; stroke-width: 2; vertical-align: middle; }

        :is(h1, h2, h3, h4, h5, h6) {
            color: #141515;
            margin: 0 0 10px 0;
            letter-spacing: -0.02em;
            line-height: 1.3em;
        }
        h3 { font-size: 17px; font-weight: 700; }
        strong, b { font-weight: 500; }

        /* FORM */
        .form-group { width: 100%; }
        .form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 15px;
            font-weight: 400;
            line-height: 1.5;
            color: #141515;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out;
        }
        .form-group.boxed {
            margin: 0;
            padding: 8px 0;
        }
        .form-group.boxed .form-control {
            background: #fff;
            box-shadow: none;
            height: 42px;
            border-radius: 6px;
            padding: 0 40px 0 16px;
            border: 1px solid #e1e1e1;
        }
        .form-group .clear-input {
            display: none;
            align-items: center;
            justify-content: center;
            color: #4f5050;
            height: 38px;
            font-size: 22px;
            position: absolute;
            right: -10px;
            bottom: 0;
            width: 32px;
            opacity: 0.5;
        }
        .form-group .input-wrapper { position: relative; }
        .form-group .input-wrapper.not-empty .clear-input { display: flex; }
        .form-group.boxed .clear-input {
            right: 0;
            height: 42px;
            width: 40px;
        }

        /* BUTTON */
        .btn {
            height: 40px;
            padding: 3px 18px;
            font-size: 13px;
            line-height: 1.2em;
            font-weight: 500;
            box-shadow: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s all;
            text-decoration: none !important;
            border-radius: 6px;
            border-width: 2px;
            cursor: pointer;
        }
        .btn-lg {
            height: 48px;
            padding: 3px 24px;
            font-size: 18px;
        }
        .btn-primary {
            background: #91623d !important;
            border-color: #91623d !important;
            color: #ffffff !important;
        }
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background: #7a5234 !important;
            border-color: #7a5234 !important;
        }

        /* ALERT */
        .alert {
            margin: 0;
            padding: 6px 16px;
            border: 0;
            font-size: 13px;
            border-radius: 6px;
        }
        .alert-outline-danger {
            background: transparent;
            color: #ec4433;
            border: 1px solid #ec4433;
        }

        /* LOGIN FORM */
        .form-button-group {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            padding-left: 16px;
            padding-right: 16px;
            background: #fff;
            min-height: 84px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }
        .password-toggle {
            position: absolute;
            right: 40px;
            top: 60%;
            transform: translateY(-50%);
            color: #666;
            font-size: 22px;
            z-index: 10;
        }
        .password-toggle:hover { color: #9c6b43; }

        /* SWEETALERT OVERRIDES */
        .swal2-close:focus { box-shadow: none !important; }
        .swal2-confirm {
            background-color: #7a5234 !important;
            border-color: #7a5234 !important;
            color: white !important;
        }
        .swal2-confirm:hover {
            background-color: #5e3e27 !important;
        }
    </style>

    <link rel="manifest" href="/manifest.json">

</head>

<body class="bg-white">

    <div id="appCapsule" class="pt-0">

        <div class="max-w-[500px] mx-auto text-center mt-1">

            <div class="px-4">
                <img src="{{ asset('assets/img/login/logo_aplikasi.png') }}" alt="image" class="w-full max-w-[200px] h-auto mx-auto">
                <h3>Silahkan masuk dengan akunmu!</h3>
            </div>

            <div class="px-4 mt-1 mb-5">

                @php
                    $messagewarning = Session::get('warning');
                @endphp

                @if (Session::get('warning'))
                    <div class="bg-transparent text-[#ec4433] border border-[#ec4433] text-[13px] rounded-md py-1.5 px-4">
                        {{ $messagewarning }}
                    </div>
                @endif

                <form action="/proseslogin" method="POST" autocomplete="off">

                    @csrf

                    <div class="w-full px-0 py-2">
                        <div class="relative">
                            <input type="text" name="nik" class="w-full h-[42px] rounded-md py-0 pl-4 pr-10 border border-gray-200 text-[15px] text-gray-900 bg-white" id="nik" placeholder="NIK">
                            <span class="clear-input hidden items-center justify-center text-gray-500 h-[42px] absolute right-0 bottom-0 w-10 opacity-50 cursor-pointer">
                                <i data-lucide="circle-x" style="width:22px;height:22px;"></i>
                            </span>
                        </div>
                    </div>

                    <div class="w-full px-0 py-2">
                        <div class="relative">
                            <input type="password" class="w-full h-[42px] rounded-md py-0 pl-4 pr-10 border border-gray-200 text-[15px] text-gray-900 bg-white" id="password" name="password" placeholder="Password">
                            <a href="#" id="togglePassword" class="absolute right-10 top-[60%] -translate-y-1/2 text-gray-500 text-[22px] z-10 hover:text-[#9c6b43]">
                                <i data-lucide="eye"></i>
                            </a>
                            <span class="clear-input hidden items-center justify-center text-gray-500 h-[42px] absolute right-0 bottom-0 w-10 opacity-50 cursor-pointer">
                                <i data-lucide="circle-x" style="width:22px;height:22px;"></i>
                            </span>
                        </div>
                    </div>

                    <div class="text-right mt-2.5">
                        <a href="#" id="forgotPassword" class="text-sm text-coklat no-underline">Lupa Password?</a>
                    </div>

                    <div class="fixed bottom-0 left-0 right-0 w-full px-4 bg-white min-h-[84px] flex items-center justify-center pb-[env(safe-area-inset-bottom)]">
                        <button type="submit" class="w-full h-12 px-6 text-lg font-medium rounded-md border-0 bg-[#91623d] text-white hover:bg-coklat">
                            Masuk
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
    <script src="{{ asset('') }}assets/js/lib/bootstrap.min.js"></script>

    <script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide)lucide.createIcons();});</script>

    <script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>
    <script src="{{ asset('assets/js/base.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $("#forgotPassword").click(function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Lupa Password?',
                text: 'Silahkan hubungi admin!',
                icon: 'warning',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#9c6b43'
            });
        });
    </script>

    <script>
        $("#togglePassword").click(function (e) {
            e.preventDefault();
            let password = $("#password");
            if (password.attr("type") === "password") {
                password.attr("type", "text");
                $(this).html('<i data-lucide="eye-off"></i>');
                if (window.lucide) lucide.createIcons();
            } else {
                password.attr("type", "password");
                $(this).html('<i data-lucide="eye"></i>');
                if (window.lucide) lucide.createIcons();
            }
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (registration) {
                        console.log('Service Worker Registered');
                    })
                    .catch(function (error) {
                        console.log('Service Worker Failed', error);
                    });
            });
        }
    </script>

</body>

</html>
