<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Dashboard Administrator WAG - Presensi Digital</title>

    @vite(['resources/css/app.css'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-6">
            <a href="/panel">
                <img src="{{ asset('assets/img/login/logo_aplikasi_admin.png') }}" alt="Logo" class="h-48 mx-auto">
            </a>
        </div>

        {{-- Card Login --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6">

                <h1 class="text-xl font-bold text-center text-slate-800 mb-6">
                    Login dengan Akun Administrator
                </h1>

                {{-- Alert --}}
                @if (Session::get('danger'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4">
                        {{ Session::get('danger') }}
                    </div>
                @endif

                {{-- Form Login --}}
                <form action="/prosesloginadmin" method="POST" autocomplete="off">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                            placeholder="Masukkan Email" autocomplete="off">
                    </div>

                    {{-- Password --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-10 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                                placeholder="Masukkan Password" autocomplete="off">
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <span class="eye-open"><i data-lucide="eye" style="width:20px;height:20px;"></i></span>
                                <span class="eye-closed" style="display:none;"><i data-lucide="eye-off" style="width:20px;height:20px;"></i></span>
                            </button>
                        </div>
                    </div>

                    {{-- Button --}}
                    <button type="submit"
                        class="w-full bg-amber-600 text-white px-4 py-2.5 rounded-lg hover:bg-amber-700 transition-colors text-sm font-medium">
                        Masuk
                    </button>

                </form>

            </div>
        </div>

    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function(e) {
            e.preventDefault();
            const password = document.getElementById('password');
            password.type = password.type === 'password' ? 'text' : 'password';
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');
            if (eyeOpen && eyeClosed) {
                eyeOpen.style.display = password.type === 'password' ? '' : 'none';
                eyeClosed.style.display = password.type === 'password' ? 'none' : '';
            }
        });
    </script>

</body>

</html>
