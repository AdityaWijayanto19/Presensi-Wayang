<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#7A5234">
    <meta name="description" content="Pasang WAG Presensi Digital ke layar utama perangkat Anda.">

    <title>Install WAG - Presensi Digital</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/login/logo_aplikasi.png') }}">
    <link rel="manifest" href="/manifest.json">

    <script>
        function installPage() {
            return {
                isIOS: false,
                isAndroid: false,
                isDesktop: false,
                isStandalone: false,
                canInstall: false,
                deferredPrompt: null,
                isInstalling: false,
                installStatus: '',

                init() {
                    this.detectDevice();
                    this.detectStandalone();
                    this.bindInstallPrompt();

                    var self = this;
                    window.addEventListener('appinstalled', function () {
                        self.deferredPrompt = null;
                        window.__deferredInstallPrompt = null;
                        self.canInstall = false;
                        self.isInstalling = false;
                        self.isStandalone = true;
                        self.installStatus = 'installed';
                        self.refreshIcons();
                    });

                    this.refreshIcons();
                },

                detectDevice() {
                    var ua = navigator.userAgent || '';
                    var iOS =
                        /iPad|iPhone|iPod/.test(ua) ||
                        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

                    this.isIOS = iOS;
                    this.isAndroid = /Android/i.test(ua);
                    this.isDesktop = !this.isIOS && !this.isAndroid;
                },

                detectStandalone() {
                    this.isStandalone =
                        window.matchMedia('(display-mode: standalone)').matches ||
                        window.navigator.standalone === true;
                },

                bindInstallPrompt() {
                    var saved = window.__deferredInstallPrompt;
                    if (saved) {
                        this.deferredPrompt = saved;
                        this.canInstall = true;
                    }

                    var self = this;
                    window.addEventListener('beforeinstallprompt', function (e) {
                        e.preventDefault();
                        self.deferredPrompt = e;
                        window.__deferredInstallPrompt = e;
                        self.canInstall = true;
                    });
                },

                async promptInstall() {
                    if (!this.deferredPrompt) {
                        this.canInstall = false;
                        window.__deferredInstallPrompt = null;
                        return;
                    }

                    try {
                        this.isInstalling = true;
                        await this.deferredPrompt.prompt();
                        var choice = await this.deferredPrompt.userChoice;

                        if (choice && choice.outcome === 'accepted') {
                            this.installStatus = 'accepted';
                        } else if (choice && choice.outcome === 'dismissed') {
                            this.installStatus = 'dismissed';
                        }
                    } catch (error) {
                        this.installStatus = 'error';
                    } finally {
                        this.deferredPrompt = null;
                        window.__deferredInstallPrompt = null;
                        this.isInstalling = false;
                        this.canInstall = false;
                    }
                },

                refreshIcons() {
                    this.$nextTick(function () {
                        if (window.lucide) lucide.createIcons();
                    });
                }
            };
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-svh bg-[#f7f5f3] antialiased">
    <div x-data="installPage()" class="min-h-svh flex items-center justify-center px-5 py-10">
        <div class="w-full max-w-[420px] text-center">

            {{-- Logo --}}
            <img src="{{ asset('assets/img/login/logo_aplikasi.png') }}" alt="WAG Presensi Digital"
                class="mx-auto w-[120px] mb-5 rounded-lg drop-shadow-[0_6px_16px_rgba(0,0,0,0.12)]">

            {{-- Title --}}
            <h1 class="text-[24px] font-bold leading-tight tracking-[-0.02em] text-[#1c1917]">
                WAG Presensi Digital
            </h1>
            <p class="mt-1.5 text-[13px] leading-relaxed text-[#57534e]">
                Akses presensi lebih cepat langsung dari layar utama perangkat.
            </p>

            {{-- Status area --}}
            <div aria-live="polite" class="mt-7">

                {{-- A. Standalone / already installed --}}
                <template x-if="isStandalone">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6">
                        <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600">
                            <i data-lucide="check-circle" style="width:22px;height:22px;"></i>
                        </div>
                        <p class="text-[15px] font-bold text-[#1c1917]">Sudah terpasang di perangkat ini.</p>
                        <p class="mt-1 text-[12px] text-[#78716c]">Buka presensi langsung dari aplikasi.</p>
                        <a href="{{ url('/presensi/create') }}"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2">
                            Buka Presensi
                        </a>
                    </div>
                </template>

                {{-- B. iOS — Add to Home Screen tutorial --}}
                <template x-if="!isStandalone && isIOS">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6 text-left">
                        <p class="text-[15px] font-bold text-[#1c1917] text-center">Tambahkan WAG ke Layar Utama</p>
                        <p class="mt-1 mb-4 text-[12px] text-[#78716c] text-center">Ikuti langkah berikut di Safari.</p>

                        <ol class="space-y-3">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#fdf8f4] border border-[#e7e5e4] text-[11px] font-bold text-[#7A5234]">1</span>
                                <div class="flex items-start gap-2 text-[13px] leading-snug text-[#44403c]">
                                    <i data-lucide="share" class="mt-0.5 shrink-0 text-[#7A5234]" style="width:16px;height:16px;"></i>
                                    <span>Ketuk tombol <strong>Bagikan</strong> di Safari.</span>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#fdf8f4] border border-[#e7e5e4] text-[11px] font-bold text-[#7A5234]">2</span>
                                <span class="text-[13px] leading-snug text-[#44403c]">Pilih <strong>Tambah ke Layar Utama</strong>.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#fdf8f4] border border-[#e7e5e4] text-[11px] font-bold text-[#7A5234]">3</span>
                                <span class="text-[13px] leading-snug text-[#44403c]">Ketuk <strong>Tambah</strong>.</span>
                            </li>
                        </ol>

                        <a href="{{ url('/presensi/create') }}"
                            class="mt-6 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2">
                            Buka Presensi
                        </a>
                    </div>
                </template>

                {{-- C. Android + beforeinstallprompt available --}}
                <template x-if="!isStandalone && isAndroid && canInstall">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6">
                        <p class="text-[15px] font-bold text-[#1c1917] text-center">Pasang WAG Presensi Digital</p>
                        <p class="mt-1 text-[12px] leading-relaxed text-[#78716c] text-center">
                            Pasang aplikasi agar akses presensi lebih cepat dari layar utama.
                        </p>

                        <button type="button"
                            @click="promptInstall()"
                            :disabled="isInstalling"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            <i data-lucide="download" style="width:18px;height:18px;"></i>
                            <span x-text="isInstalling ? 'Memproses…' : 'Install WAG'">Install WAG</span>
                        </button>

                        <div class="mt-3 text-center">
                            <span x-show="installStatus === 'dismissed'" class="block text-[12px] text-[#a8a29e]">
                                Pemasangan dibatalkan.
                            </span>

                            <a href="{{ url('/presensi/create') }}"
                                class="mt-3 inline-flex h-11 items-center justify-center rounded-lg px-5 text-[13px] font-semibold text-[#7A5234] hover:bg-[#fdf8f4] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40">
                                Buka Presensi
                            </a>
                        </div>
                    </div>
                </template>

                {{-- D. Android without install prompt --}}
                <template x-if="!isStandalone && isAndroid && !canInstall">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6">
                        <p class="text-[15px] font-bold text-[#1c1917] text-center">Tambahkan WAG ke Layar Utama</p>
                        <p class="mt-1.5 text-[12px] leading-relaxed text-[#78716c] text-center">
                            Opsi pemasangan tidak tersedia di browser ini.
                            Anda dapat mencoba membuka halaman ini menggunakan browser yang mendukung pemasangan aplikasi web.
                        </p>

                        <a href="{{ url('/presensi/create') }}"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2">
                            Buka Presensi
                        </a>
                    </div>
                </template>

                {{-- E. Desktop / other non-installable --}}
                <template x-if="!isStandalone && !isIOS && !isAndroid && isDesktop">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6">
                        <p class="text-[15px] font-bold text-[#1c1917] text-center">WAG Presensi Digital</p>
                        <p class="mt-1.5 text-[12px] leading-relaxed text-[#78716c] text-center">
                            Gunakan WAG Presensi Digital melalui browser.
                        </p>

                        <a href="{{ url('/presensi/create') }}"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2">
                            Buka Presensi
                        </a>
                    </div>
                </template>

                {{-- Fallback: belum ada state yang cocok (mis. deteksi belum selesai / platform aneh) --}}
                <template x-if="!isStandalone && !isIOS && !isAndroid && !isDesktop">
                    <div class="rounded-lg border border-[#e7e5e4] bg-white px-5 py-6">
                        <p class="text-[15px] font-bold text-[#1c1917] text-center">WAG Presensi Digital</p>
                        <a href="{{ url('/presensi/create') }}"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center rounded-lg bg-[#7A5234] px-6 text-[14px] font-semibold text-white transition-colors hover:bg-[#5e3e27] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7A5234]/40 focus-visible:ring-offset-2">
                            Buka Presensi
                        </a>
                    </div>
                </template>

            </div>

            <p class="mt-6 text-[11px] text-[#a8a29e]">WAG — Presensi Digital</p>
        </div>
    </div>
</body>

</html>
