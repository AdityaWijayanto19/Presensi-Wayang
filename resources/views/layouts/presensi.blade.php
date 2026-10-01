<!DOCTYPE html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="WAG-Presensi">

    <meta name="theme-color" content="#7A5234">
    <meta name="description" content="WAG Presensi Digital">

    <title>WAG - Presensi Digital</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">

    <link rel="apple-touch-icon" href="/icons/icon_192.png">

    <link rel="manifest" href="/manifest.json">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        function offlineBanner() {
            return {
                isOffline: false,
                showOnline: false,
                _onlineTimer: null,

                init() {
                    this.isOffline = !navigator.onLine;
                    var self = this;

                    window.addEventListener('offline', function () {
                        self.isOffline = true;
                        self.showOnline = false;
                    });

                    window.addEventListener('online', function () {
                        self.isOffline = false;
                        self.showOnline = true;
                        clearTimeout(self._onlineTimer);
                        self._onlineTimer = setTimeout(function () {
                            self.showOnline = false;
                        }, 3000);
                    });
                }
            };
        }
    </script>

    {{-- Banner ajakan mengaktifkan notifikasi, tampil di seluruh halaman karyawan.
         Izin browser TIDAK pernah diminta dari sini: hanya lewat tombol (gestur user). --}}
    <script>
        function pushBanner() {
            var CONTENT = {
                install: {
                    title: 'Tambahkan ke Layar Utama',
                    desc: 'Di iPhone, notifikasi hanya bisa aktif setelah aplikasi dipasang ke Layar Utama.',
                    action: 'Lihat Cara'
                },
                enable: {
                    title: 'Aktifkan Notifikasi',
                    desc: 'Terima pengingat WFH, izin, dan lembur walau aplikasi sedang ditutup.',
                    action: 'Aktifkan'
                },
                sync: {
                    title: 'Sinkronkan Notifikasi',
                    desc: 'Langganan notifikasi belum tercatat di server.',
                    action: 'Sinkronkan'
                },
                blocked: {
                    title: 'Notifikasi Diblokir',
                    desc: 'Izin ditolak oleh browser. Aktifkan kembali lewat pengaturan situs ini.',
                    action: null
                }
            };

            return {
                visible: false,
                mode: 'hidden',
                title: '',
                desc: '',
                action: null,
                busy: false,

                init() {
                    if (!window.WAGPush) return;

                    window.WAGPush.onChange((state) => this.apply(state));
                    this.apply(window.WAGPush.getState());

                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible' && window.WAGPush) {
                            this.apply(window.WAGPush.getState());
                        }
                    });

                    if (window.lucide) window.lucide.createIcons();
                },

                apply(state) {
                    if (!state) return this.hide();

                    // iOS tanpa konteks Layar Utama: WebKit tidak mengekspos Notification
                    // dan PushManager sama sekali, jadi tombol izin tidak akan berfungsi.
                    if (state.isIOS && !state.standalone) return this.setMode('install');
                    if (!state.supported) return this.hide();
                    if (state.permission === 'granted' && state.hasSub) return this.hide();
                    if (state.permission === 'granted') return this.setMode('sync');
                    if (state.permission === 'denied') return this.setMode('blocked');

                    return this.setMode('enable');
                },

                setMode(mode) {
                    var content = CONTENT[mode];

                    this.mode = mode;
                    this.title = content.title;
                    this.desc = content.desc;
                    this.action = content.action;
                    this.visible = !this.dismissedFor(mode);
                },

                hide() {
                    this.visible = false;
                },

                dismissedFor(mode) {
                    try {
                        return window.localStorage.getItem('wag_push_banner_' + mode) === '1';
                    } catch (error) {
                        return false;
                    }
                },

                dismiss() {
                    try {
                        window.localStorage.setItem('wag_push_banner_' + this.mode, '1');
                    } catch (error) {
                        // Penyimpanan tidak tersedia (private mode) — cukup tutup untuk sesi ini.
                    }
                    this.visible = false;
                },

                runPrimary() {
                    if (this.mode === 'install') {
                        window.location.href = '/install';
                        return;
                    }
                    if (this.mode === 'sync') return this.syncNow();
                    if (this.mode === 'enable') return this.enablePush();
                },

                // HARUS dipanggil langsung dari klik tombol ini.
                // WebKit/iOS menuntut requestPermission() berada di dalam gestur user.
                async enablePush() {
                    if (this.busy) return;
                    this.busy = true;

                    try {
                        var result = await window.WAGPush.enable();
                        this.apply(window.WAGPush.getState());

                        if (result.ok) {
                            this.toast('success', 'Notifikasi berhasil diaktifkan');
                        } else if (result.reason === 'denied') {
                            this.toast('error', 'Izin notifikasi ditolak oleh browser');
                        }
                    } finally {
                        this.busy = false;
                    }
                },

                async syncNow() {
                    if (this.busy) return;
                    this.busy = true;

                    try {
                        await window.WAGPush.syncExisting();
                        this.apply(window.WAGPush.getState());
                        this.toast('success', 'Langganan notifikasi tersinkron');
                    } finally {
                        this.busy = false;
                    }
                },

                toast(type, message) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(type, message);
                    }
                }
            };
        }
    </script>

    {{-- Util Web Push dipakai bersama oleh halaman dan Service Worker. --}}
    <script src="{{ asset('js/push-util.js') }}"></script>

    {{-- Web Push client. Satu-satunya sumber logika subscription di sisi klien.
         Dideklarasikan di <head> agar sudah tersedia saat Alpine menginisialisasi komponen banner. --}}
    <script>
        window.WAGPush = (function () {
            'use strict';

            var SUBSCRIBE_URL = '/api/push/subscribe';
            var VAPID_PUBLIC_KEY = @json(config('webpush.vapid.public_key'));
            var CSRF_TOKEN = '{{ csrf_token() }}';

            var listeners = [];
            var state = {
                supported: false,
                permission: 'default',
                hasSub: false,
                standalone: false,
                isIOS: false
            };

            function refresh() {
                state.supported = 'serviceWorker' in navigator
                    && 'PushManager' in window
                    && 'Notification' in window;
                state.permission = state.supported ? Notification.permission : 'unsupported';
                state.standalone = window.navigator.standalone === true
                    || (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches);
                state.isIOS = /iPhone|iPad|iPod/.test(navigator.userAgent);
                return state;
            }

            function notify() {
                refresh();
                listeners.forEach(function (listener) {
                    try {
                        listener(state);
                    } catch (error) {
                        // Listener pihak ketiga tidak boleh menggagalkan sinkronisasi.
                    }
                });
            }

            function postSubscription(subscription) {
                var sub = subscription.toJSON();
                return fetch(SUBSCRIBE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        endpoint: sub.endpoint,
                        public_key: sub.keys.p256dh,
                        auth_token: sub.keys.auth
                    })
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('subscribe_failed_' + response.status);
                    }
                    state.hasSub = true;
                    notify();
                });
            }

            function subscribeNew(registration) {
                if (!window.WAGPushUtil) {
                    return Promise.reject(new Error('push_util_unavailable'));
                }

                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: window.WAGPushUtil.urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
                });
            }

            function ensureRegistration() {
                return navigator.serviceWorker.getRegistration().then(function (registration) {
                    return registration || navigator.serviceWorker.register('/sw.js');
                });
            }

            // Self-heal utama: pastikan subscription yang sudah ada di browser selalu
            // tercatat di database. Menutup kasus baris terhapus saat logout di perangkat
            // lain, dan kasus endpoint yang berganti di tengah jalan.
            function syncExisting() {
                if (!refresh().supported) {
                    return Promise.resolve(false);
                }

                return ensureRegistration()
                    .then(function () {
                        return navigator.serviceWorker.ready;
                    })
                    .then(function (registration) {
                        return registration.pushManager.getSubscription().then(function (subscription) {
                            if (subscription) {
                                return postSubscription(subscription).then(function () {
                                    return true;
                                });
                            }

                            // Izin sudah granted tetapi subscription hilang: buat ulang
                            // tanpa memunculkan prompt apa pun.
                            if (Notification.permission === 'granted') {
                                return subscribeNew(registration)
                                    .then(postSubscription)
                                    .then(function () {
                                        return true;
                                    });
                            }

                            // permission 'default' / 'denied': requestPermission() TIDAK dipanggil
                            // dari sini. Hak izin hanya diminta lewat gestur user (lihat enable()).
                            state.hasSub = false;
                            notify();
                            return false;
                        });
                    })
                    .catch(function (error) {
                        console.log('Push subscription sync failed', error);
                        return false;
                    });
            }

            // WAJIB dipanggil sinkron dari dalam click handler.
            // WebKit/iOS menuntut Notification.requestPermission() berada di dalam
            // transient activation, sehingga tidak boleh ada await sebelumnya.
            function enable() {
                if (!refresh().supported) {
                    return Promise.resolve({ ok: false, reason: 'unsupported' });
                }

                var permissionPromise = Notification.requestPermission();

                return permissionPromise.then(function (permission) {
                    state.permission = permission;
                    notify();

                    if (permission !== 'granted') {
                        return {
                            ok: false,
                            reason: permission === 'denied' ? 'denied' : 'dismissed'
                        };
                    }

                    return ensureRegistration()
                        .then(function () {
                            return navigator.serviceWorker.ready;
                        })
                        .then(function (registration) {
                            return registration.pushManager.getSubscription().then(function (subscription) {
                                return subscription || subscribeNew(registration);
                            }).then(postSubscription);
                        })
                        .then(function () {
                            return { ok: true };
                        })
                        .catch(function (error) {
                            console.log('Push subscription failed', error);
                            return { ok: false, reason: 'error' };
                        });
                });
            }

            function init() {
                refresh();

                if (!state.supported) {
                    notify();
                    return;
                }

                syncExisting();

                var syncing = false;
                document.addEventListener('visibilitychange', function () {
                    if (document.visibilityState !== 'visible' || syncing) return;
                    syncing = true;
                    syncExisting().then(function () {
                        syncing = false;
                    });
                });
            }

            return {
                init: init,
                syncExisting: syncExisting,
                enable: enable,
                getState: function () {
                    return refresh();
                },
                onChange: function (listener) {
                    listeners.push(listener);
                }
            };
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="bg-[#e9ecef]">

    <style>
        :root {
            --header-pattern: url('{{ asset('assets/img/bg-mega-mendung.webp') }}');
        }
    </style>

    {{-- Offline / online status banner --}}
    <div x-data="offlineBanner()" x-init="init()" x-cloak
        x-show="isOffline || showOnline"
        role="status"
        aria-live="polite"
        class="fixed top-[env(safe-area-inset-top)] left-0 right-0 z-[1000] px-4 py-2 text-center text-[12px] font-semibold text-white shadow-sm"
        :class="isOffline ? 'bg-[#7f1d1d]' : 'bg-emerald-700'">
        <span x-show="isOffline">Koneksi terputus. Periksa jaringan Anda.</span>
        <span x-show="!isOffline && showOnline">Terhubung kembali.</span>
    </div>

    {{-- Banner Aktifkan Notifikasi --}}
    <div x-data="pushBanner()" x-init="init()" x-cloak
        x-show="visible"
        role="status"
        aria-live="polite"
        class="fixed left-0 right-0 z-[999] px-3 bottom-[calc(70px_+_env(safe-area-inset-bottom))] lg:bottom-6 lg:px-6">
        <div class="mx-auto max-w-lg rounded-2xl bg-white border border-[#f0ece8] p-3.5 shadow-[0_8px_28px_rgba(28,25,23,0.16)]"
            style="animation: modalIn 0.2s ease;">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0">
                    <i data-lucide="bell" style="width:18px;height:18px;"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[13px] font-bold text-[#1c1917] leading-tight" x-text="title"></div>
                    <div class="text-[11px] text-[#78716c] mt-1" x-text="desc"></div>
                </div>
                <button type="button" @click="dismiss()" aria-label="Tutup"
                    class="w-7 h-7 rounded-full inline-flex items-center justify-center bg-[#f5f5f4] border border-[#e7e5e4] text-[#57534e] cursor-pointer shrink-0">
                    <i data-lucide="x" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="button" x-show="action" x-text="busy ? 'Memproses…' : action"
                    @click="runPrimary()" :disabled="busy"
                    class="flex-1 rounded-full px-4 py-2 text-[12px] font-bold bg-coklat text-white border-0 cursor-pointer disabled:opacity-60">
                </button>
                <button type="button" @click="dismiss()"
                    class="rounded-full px-4 py-2 text-[12px] font-semibold bg-white border border-[#e7e5e4] text-[#44403c] cursor-pointer">
                    Nanti
                </button>
            </div>
        </div>
    </div>

    {{-- Sidebar Desktop (lg+) --}}
    @include('layouts.sidebarNav')

    <div class="lg:pl-56">
        {{-- Header --}}
        @yield('header')

        {{-- App Content --}}
        {{-- padding-top (bukan margin) supaya offset aman-atas + offset header 56px tidak ikut
             margin-collapse dengan margin-top halaman anak (yang bikin konten ketutup header di PWA) --}}
        <div id="appCapsule" class="pt-[env(safe-area-inset-top)] pb-[calc(70px_+_env(safe-area-inset-bottom))] lg:pb-6">

            @yield('content')

        </div>
    </div>

    {{-- Bottom Navigation --}}
    @include('layouts.bottomNav')

    {{-- Global Alert Toast (success / error / info / warning) --}}
    <x-admin.alert />

    {{-- File Preview Modal (global) --}}
    <div id="filePreviewBackdrop"
        class="file-modal-backdrop fixed inset-0 z-[99999] bg-[rgba(20,12,6,0.55)] backdrop-blur-sm hidden items-center justify-center p-4"
        aria-hidden="true">
        <div class="bg-white rounded-2xl w-full max-w-[640px] max-h-[85vh] flex flex-col overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.3)]"
            style="animation: modalIn 0.2s ease;" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#f0ece8] shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div id="fileModalIcon"
                        class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#fdf8f4] border border-[#f0ece8] text-coklat shrink-0">
                        <i data-lucide="file-text" style="width:18px;height:18px;"></i>
                    </div>
                    <div class="min-w-0">
                        <div id="fileModalTitle" class="text-[14px] font-bold text-[#1a1a1a] leading-tight truncate">
                            Preview Dokumen</div>
                        <div id="fileModalSubtitle" class="text-[11px] text-[#a8a29e] truncate">Memuat...</div>
                    </div>
                </div>
                <button type="button"
                    class="w-9 h-9 rounded-full inline-flex items-center justify-center bg-[#f5f5f4] border border-[#e7e5e4] text-[#57534e] cursor-pointer hover:bg-[#e7e5e4] shrink-0"
                    id="fileModalClose" aria-label="Tutup">
                    <i data-lucide="x" style="width:20px;height:20px;"></i>
                </button>
            </div>
            <div id="fileModalBody" class="flex-1 overflow-hidden bg-[#fafaf9] flex flex-col min-h-[320px]">
                <div id="fileModalLoader"
                    class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center">
                    <i data-lucide="hourglass" class="text-[36px] text-[#d6c7b8] animate-pulse"
                        style="width:36px;height:36px;"></i>
                    <p class="text-[13px] text-[#78716c]">Memuat preview...</p>
                </div>
            </div>
            <div class="flex gap-2 p-3.5 px-5 border-t border-[#f0ece8] flex-wrap justify-end bg-white">
                <a id="fileModalDownload" href="#" download
                    class="inline-flex items-center gap-1.5 py-2 px-4 rounded-full text-[13px] font-semibold border-[1.5px] cursor-pointer leading-none no-underline bg-white border-[#e7e5e4] text-[#44403c] hover:border-[#a8a29e]"
                    rel="noopener">
                    <i data-lucide="download"></i> Download
                </a>
                <a id="fileModalOpenTab" href="#" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 py-2 px-4 rounded-full text-[13px] font-semibold border-[1.5px] cursor-pointer leading-none no-underline bg-coklat border-coklat text-white hover:bg-coklat-dark hover:border-coklat-dark">
                    <i data-lucide="external-link"></i> Buka di Tab Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Script --}}
    @include('layouts.script')

    <script>
        (function() {
            // prevent double binding on soft navigation
            if (window.__filePreviewBound) return;
            window.__filePreviewBound = true;

            const backdrop = document.getElementById('filePreviewBackdrop');
            const body = document.getElementById('fileModalBody');
            const loader = document.getElementById('fileModalLoader');
            const titleEl = document.getElementById('fileModalTitle');
            const subtitleEl = document.getElementById('fileModalSubtitle');
            const downloadEl = document.getElementById('fileModalDownload');
            const openTabEl = document.getElementById('fileModalOpenTab');
            const closeBtn = document.getElementById('fileModalClose');

            function openPreview(url, filename, label) {
                const ext = (filename.split('.').pop() || '').toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
                const isPdf = ext === 'pdf';
                const isDoc = ['doc', 'docx'].includes(ext);

                titleEl.textContent = label || filename;
                subtitleEl.textContent = filename;
                downloadEl.href = url;

                // For doc/docx: hide "Buka di Tab Baru" (browser will download anyway), show only Download with clear message
                if (isDoc) {
                    openTabEl.style.display = 'none';
                    downloadEl.style.display = '';
                    downloadEl.innerHTML = '<i data-lucide="download"></i> Download';
                } else {
                    openTabEl.href = url;
                    openTabEl.style.display = '';
                    downloadEl.style.display = '';
                    downloadEl.innerHTML = '<i data-lucide="download"></i> Download';
                    openTabEl.innerHTML = '<i data-lucide="external-link"></i> Buka di Tab Baru';
                }

                backdrop.classList.add('open');
                document.body.style.overflow = 'hidden';

                if (isImage) {
                    body.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = filename;
                    img.onerror = () => {
                        showDocFallback(ext, filename, label);
                    };
                    body.appendChild(img);
                    return;
                }
                if (isPdf) {
                    body.innerHTML = '';
                    const iframe = document.createElement('iframe');
                    iframe.src = url;
                    iframe.title = filename;
                    iframe.loading = 'lazy';
                    body.appendChild(iframe);
                    return;
                }
                // doc/docx and others: show clean file-card fallback (only PDF & images get iframe preview)
                showDocFallback(ext, filename, label);
                if (window.lucide) lucide.createIcons();
            }

            function showDocFallback(ext, filename, label) {
                const isDoc = ['doc', 'docx'].includes(ext);
                const title = isDoc ? 'File Word — Preview terbatas' : 'Preview tidak tersedia untuk .' + ext;
                const desc = isDoc ?
                    'Dokumen <b>' + filename + '</b> berformat <b>.' + ext +
                    '</b> tidak bisa preview langsung di browser. Silakan <b>Download</b> untuk buka di Microsoft Word. <br><br><span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#7a5234] bg-[#fdf8f4] border border-[#f0ece8] rounded-full px-2.5 py-1"><i data-lucide="lightbulb" style="width:14px;height:14px;"></i> Tips: upload sebagai <b>PDF</b> agar bisa preview langsung di sini.</span>' :
                    'File .' + ext + ' tidak bisa di-preview langsung. Silakan buka di tab baru atau download.';
                body.innerHTML = '<div class="flex flex-col items-center gap-4 p-6 text-center">' +
                    '<div class="w-20 h-20 rounded-2xl bg-[#fdf8f4] border border-[#f0ece8] flex items-center justify-center text-coklat shadow-sm"><i data-lucide="file-text" style="width:36px;height:36px;"></i></div>' +
                    '<div class="w-full max-w-[32ch]">' +
                    '<p class="text-[14px] font-bold text-[#1c1917]">' + title + '</p>' +
                    '<p class="mt-1.5 text-[12px] leading-relaxed text-[#57534e]">' + desc + '</p>' +
                    '<div class="mt-3 inline-flex items-center gap-2 bg-white border border-[#f0ece8] rounded-full px-3 py-1.5 shadow-sm">' +
                    '<i data-lucide="file" class="text-[#a8a29e]" style="width:16px;height:16px;"></i>' +
                    '<span class="text-[11px] font-mono font-semibold text-[#44403c] truncate max-w-[18ch]">' +
                    filename + '</span>' +
                    '<span class="inline-flex items-center justify-center min-w-[28px] h-5 px-1.5 rounded-full bg-[#1c1917] text-white text-[10px] font-bold uppercase">' +
                    ext + '</span>' +
                    '</div>' +
                    '</div></div>';
            }

            function showFallback(ext, url, customMsg) {
                // legacy wrapper for image error
                showDocFallback(ext, url.split('/').pop() || ext, '');
            }

            function closePreview() {
                backdrop.classList.remove('open');
                document.body.style.overflow = '';
                // restore footer buttons for next preview
                downloadEl.style.display = '';
                openTabEl.style.display = '';
                setTimeout(() => {
                    body.innerHTML =
                        '<div id="fileModalLoader" class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center"><i data-lucide="hourglass" class="text-[#d6c7b8] animate-pulse" style="width:36px;height:36px;"></i><p class="text-[13px] text-[#78716c]">Memuat preview...</p></div>';
                    if (window.lucide) lucide.createIcons();
                }, 200);
            }

            document.addEventListener('click', function(e) {
                const pill = e.target.closest('.js-preview');
                if (pill) {
                    e.preventDefault();
                    e.stopPropagation();
                    // stopImmediate to prevent double firing if script bound twice
                    if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                    openPreview(pill.dataset.url, pill.dataset.filename || '', pill.dataset.label || '');
                }
            }, true);
            // Laporan text preview (no file, only deskripsi)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-preview-laporan');
                if (btn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const deskripsi = btn.dataset.deskripsi || '';
                    const tgl = btn.dataset.tgl || '';
                    const label = btn.dataset.label || 'Laporan WFH — ' + tgl;
                    titleEl.textContent = label;
                    subtitleEl.textContent = 'Laporan • ' + tgl;
                    downloadEl.style.display = 'none';
                    openTabEl.style.display = 'none';
                    backdrop.classList.add('open');
                    document.body.style.overflow = 'hidden';
                    const safe = deskripsi.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/\n/g, '<br>');
                    body.innerHTML = '<div class="p-5">' +
                        '<div class="flex items-center gap-2 mb-3"><div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700"><i data-lucide="clipboard" style="width:18px;height:18px;"></i></div><div><div class="text-[13px] font-bold text-[#1c1917]">' +
                        label + '</div><div class="text-[11px] text-[#78716c]">' + tgl + '</div></div></div>' +
                        '<div class="bg-[#fdf8f4] border border-[#f0ece8] rounded-xl p-4 text-[13px] leading-relaxed text-[#1c1917] whitespace-pre-wrap" style="max-height:50vh; overflow:auto;">' +
                        safe + '</div>' +
                        '</div>';
                    if (window.lucide) lucide.createIcons();
                }
            }, true);
            // Direct open in new tab — explicit handler to avoid popup blocker / href timing issues
            openTabEl.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url && url !== '#') {
                    const win = window.open(url, '_blank', 'noopener');
                    // fallback if popup blocked
                    if (!win) {
                        window.location.href = url;
                    }
                }
            });
            // Download — force download via temporary anchor to ensure it works even when server sends inline
            downloadEl.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const filename = subtitleEl.textContent || (this.getAttribute('download') || '');
                if (!url || url === '#') return;
                // Create temporary link with download attribute — works for same-origin
                const a = document.createElement('a');
                a.href = url;
                if (filename && filename !== '#') a.download = filename;
                else a.setAttribute('download', '');
                document.body.appendChild(a);
                a.click();
                a.remove();
                // Fallback: if browser ignored download (e.g., popup blocker), open in new tab
                setTimeout(() => {
                    // if still on same page and no download started, try direct navigation
                    // no-op, download should have started
                }, 200);
            });

            closeBtn.addEventListener('click', closePreview);
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) closePreview();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && backdrop.classList.contains('open')) closePreview();
            });
            window.openFilePreview = openPreview;
            window.closeFilePreview = closePreview;
        })();
    </script>

    {{-- Inisialisasi Web Push. Logika lengkapnya ada di window.WAGPush (bagian <head>).
         Pendaftaran service worker dan sinkronisasi subscription dilakukan di sini;
         requestPermission() tidak pernah dipanggil otomatis, hanya lewat gestur user. --}}
    <script>
        window.WAGPush && window.WAGPush.init();
    </script>

    {{-- PWA Standalone Detection --}}
    <script>
        if (window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches) {
            document.documentElement.classList.add('pwa-standalone');
        }
    </script>

</body>

</html>
