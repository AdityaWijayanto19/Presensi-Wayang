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

    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="bg-[#e9ecef]">

    <style>
        :root {
            --header-pattern: url('{{ asset('assets/img/bg-mega-mendung.webp') }}');
        }
    </style>

    {{-- Header --}}
    @yield('header')

    {{-- App Content --}}
    <div id="appCapsule" class="mt-[env(safe-area-inset-top)] pb-[70px]">

        @yield('content')

    </div>

    {{-- Bottom Navigation --}}
    @include('layouts.bottomNav')

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

    {{-- Service Worker & Push Subscription --}}
    <script>
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker Registered');
                        return registration.pushManager.getSubscription();
                    })
                    .then(function(subscription) {
                        if (!subscription) {
                            return Notification.requestPermission().then(function(permission) {
                                if (permission === 'granted') {
                                    return registerPush();
                                }
                            });
                        }
                    })
                    .catch(function(error) {
                        console.log('Service Worker Failed', error);
                    });
            });
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        async function registerPush() {
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('{{ config('webpush.vapid.public_key') }}')
                });
                const sub = subscription.toJSON();
                await fetch('/api/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        endpoint: sub.endpoint,
                        public_key: sub.keys.p256dh,
                        auth_token: sub.keys.auth
                    })
                });
                console.log('Push subscription saved');
            } catch (error) {
                console.log('Push subscription failed', error);
            }
        }
    </script>

    {{-- PWA Standalone Detection --}}
    <script>
        if (window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches) {
            document.documentElement.classList.add('pwa-standalone');
        }
    </script>

</body>

</html>
