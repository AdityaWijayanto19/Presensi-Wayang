<!DOCTYPE html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

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

    <link rel="icon"
        type="image/png"
        href="{{ asset('assets/img/login/logo_aplikasi.png') }}"
        sizes="32x32">

    <link rel="apple-touch-icon"
        href="/icons/icon_192.png">

    <link rel="manifest"
        href="/manifest.json">

    {{-- Lucide Icons loaded via script.blade.php --}}

    <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        crossorigin="">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
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
        img, video, canvas { display: block; max-width: 100%; }
        img { height: auto; }
        a { color: inherit; text-decoration: none; }
        input, button, textarea, select { font: inherit; }
        table { border-collapse: collapse; border-spacing: 0; }

        /* BASE */
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
            -moz-osx-font-smoothing: grayscale;
        }

        ::-webkit-scrollbar { width: 0; }
        button { outline: 0 !important; }
        button:hover, button:active, button:focus { outline: 0 !important; }
        i[data-lucide] { width: 22px; height: 22px; stroke-width: 2; vertical-align: middle; }

        /* TYPOGRAPHY */
        :is(h1, h2, h3, h4, h5, h6) {
            color: #141515;
            margin: 0 0 10px 0;
            letter-spacing: -0.02em;
            line-height: 1.3em;
        }

        h1 { font-size: 34px; font-weight: 700; }
        h2 { font-size: 24px; font-weight: 700; }
        h3 { font-size: 17px; font-weight: 700; }
        h4 { font-size: 15px; font-weight: 500; }
        h5 { font-size: 13px; font-weight: 500; }
        h6 { font-size: 11px; font-weight: 500; }
        strong, b { font-weight: 500; }

        /* APP HEADER */
        .appHeader {
            min-height: 56px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: env(safe-area-inset-top);
            left: 0;
            right: 0;
            z-index: 999;
            background: #fff;
            color: #141515;
            box-shadow: 0 3px 6px 0 rgba(0, 0, 0, 0.1), 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        }
        .appHeader .left,
        .appHeader .right {
            height: 56px;
            display: flex;
            align-items: center;
            position: absolute;
        }
        .appHeader .left i[data-lucide],
        .appHeader .right i[data-lucide] {
            width: 26px;
            height: 26px;
            stroke-width: 2.5;
        }
        .appHeader .left .headerButton,
        .appHeader .right .headerButton {
            min-width: 36px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            color: #141515;
            position: relative;
        }
        .appHeader .left { left: 8px; top: 0; }
        .appHeader .right { right: 8px; top: 0; }
        .appHeader .pageTitle {
            font-size: 17px;
            font-weight: 500;
            padding: 0 10px;
        }
        .appHeader.text-light { color: #fff; }
        .appHeader.text-light .headerButton,
        .appHeader.text-light .pageTitle { color: #fff; }
        .appHeader[class*="bg-"] { border: 0; }

        /* SECTION */
        .section { padding: 0 16px; }

        /* TEXT MUTED */
        .text-muted {
            font-size: 13px;
            color: #4f5050 !important;
        }

        /* APP BOTTOM MENU */
        .appBottomMenu {
            min-height: 56px;
            position: fixed;
            z-index: 99999;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top: 1px solid #e1e1e1;
            padding-left: 4px;
            padding-right: 4px;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .appBottomMenu .disabled { pointer-events: none; }
        .appBottomMenu .item {
            font-size: 9px;
            letter-spacing: 0;
            text-align: center;
            width: 100%;
            height: 56px;
            line-height: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            text-decoration: none !important;
        }
        .appBottomMenu .item .col {
            width: 100%;
            padding: 0 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .appBottomMenu .item i[data-lucide] {
            display: block;
            margin-top: 1px;
            margin-bottom: 3px;
            width: 26px;
            height: 26px;
            line-height: 1em;
            color: #141515;
            transition: 0.1s all;
        }
        .appBottomMenu .item strong {
            margin-top: 4px;
            display: block;
            color: #141515;
            font-weight: 400;
            transition: 0.1s all;
        }
        .appBottomMenu .item:active {
            opacity: 0.8;
            text-decoration: none !important;
        }
        .appBottomMenu .item.active i[data-lucide],
        .appBottomMenu .item.active strong {
            color: #7a5234 !important;
            font-weight: 500;
        }
        .appBottomMenu .item:hover i[data-lucide],
        .appBottomMenu .item:hover strong {
            color: #bdb4b4;
        }

        /* APP CAPSULE */
        #appCapsule {
            margin-top: env(safe-area-inset-top);
            padding-bottom: 70px;
        }

        /* LISTVIEW */
        .listview {
            display: block;
            padding: 0;
            margin: 0;
            color: #141515;
            background: #fff;
            border-top: 1px solid #e1e1e1;
            border-bottom: 1px solid #e1e1e1;
            line-height: 1.3em;
        }
        .listview > li {
            padding: 8px 16px;
            display: block;
            align-items: center;
            justify-content: space-between;
            position: relative;
            min-height: 50px;
        }
        .listview > li::after {
            content: "";
            display: block;
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 1px;
            background: #e1e1e1;
        }
        .listview > li:last-child::after { display: none; }
        .image-listview > li { padding: 0; }
        .image-listview > li::after { left: 68px; }
        .image-listview > li .item {
            padding: 10px 16px;
            width: 100%;
            min-height: 50px;
            display: flex;
            align-items: center;
        }
        .image-listview > li .item .in {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .image-listview > li a.item {
            color: #141515 !important;
            padding-right: 36px;
        }
        .image-listview > li a.item:active {
            background: rgba(225, 225, 225, 0.3);
        }

        /* CARD */
        .card {
            background: #ffffff;
            border-radius: 6px;
            border: 0;
            box-shadow: 0 3px 6px 0 rgba(0, 0, 0, 0.1), 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        }
        .card .card-body {
            padding: 24px 16px;
            line-height: 1.4em;
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
        .btn i[data-lucide] {
            width: 22px;
            height: 22px;
            margin-right: 10px;
            margin-top: -2px;
            font-weight: 700;
        }
        .btn-lg {
            height: 48px;
            padding: 3px 24px;
            font-size: 18px;
        }
        .btn-lg i[data-lucide] { width: 26px; height: 26px; }
        .btn-primary {
            background: #91623d !important;
            border-color: #91623d !important;
            color: #ffffff !important;
        }
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary.active {
            background: #7a5234 !important;
            border-color: #7a5234 !important;
        }
        .btn-danger {
            background: #ec4433 !important;
            border-color: #ec4433 !important;
            color: #ffffff !important;
        }
        .btn-danger:hover,
        .btn-danger:focus,
        .btn-danger:active,
        .btn-danger.active {
            background: #ea2f1c !important;
            border-color: #ea2f1c !important;
        }

        /* ALERT */
        .alert {
            margin: 0;
            padding: 6px 16px;
            border: 0;
            font-size: 13px;
            border-radius: 6px;
        }
        .alert-success {
            background: #34c759;
            color: #fff;
            border: 1px solid #34c759;
        }
        .alert-danger {
            background: #ec4433;
            color: #fff;
            border: 1px solid #ec4433;
        }
        .alert-outline-warning {
            background: transparent;
            color: #fe9500;
            border: 1px solid #fe9500;
        }
        .alert-outline-danger {
            background: transparent;
            color: #ec4433;
            border: 1px solid #ec4433;
        }

        /* FORM */
        .form-group { width: 100%; }
        textarea,
        .form-control {
            background-clip: padding-box;
            background-image: linear-gradient(transparent, transparent);
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

        /* FAB BUTTON */
        .fab-button .fab {
            box-shadow: 0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 100%;
            color: #fff !important;
        }
        .fab-button .fab > i[data-lucide] {
            width: 26px;
            height: 26px;
            transition: 0.2s all;
            stroke-width: 2.5;
        }
        .fab-button.bottom-right {
            position: fixed;
            bottom: 16px;
            right: 16px;
            z-index: 100;
            margin-bottom: env(safe-area-inset-bottom);
        }
        .fab-button.bottom-right span {
            position: absolute;
            bottom: 65px;
            left: 45%;
            transform: translateX(-50%);
            text-align: center;
            color: black;
            font-size: 12px;
            white-space: nowrap;
        }

        /* NAV TABS */
        .nav-tabs.style1 {
            border: 0;
            background: rgba(225, 225, 225, 0.4);
            border-radius: 6px;
            display: flex;
        }
        .nav-tabs.style1 .nav-item {
            flex: 1;
            width: 100%;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        .nav-tabs.style1 .nav-item .nav-link {
            color: #4f5050;
            font-weight: 500;
            font-size: 13px;
            border: 0 !important;
            line-height: 1.2em;
            width: 100%;
            border-radius: 6px;
            padding: 6px 10px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px !important;
        }
        .nav-tabs.style1 .nav-item .nav-link.active {
            color: #141515;
            box-shadow: 0 3px 6px 0 rgba(0, 0, 0, 0.1), 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        }

        /* WEBCAM */
        .webcam-capture {
            max-width: 380px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 15px 15px 0 0;
            line-height: 0;
        }
        @media (min-width: 768px) {
            .webcam-capture { max-width: 100%; border-radius: 15px; }
        }
        .webcam-capture > div:empty {
            display: none;
        }
        .webcam-capture video {
            margin: 0;
            padding: 0;
            display: block;
        }
        .webcam-capture video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            display: block;
            transform: scaleX(-1);
        }

        /* MAP */
        #map {
            height: 280px;
            border-radius: 15px;
            overflow: hidden;
        }
        @media (min-width: 768px) {
            #map { height: 400px; }
        }

        /* BADGE */
        .badge {
            font-size: 12px;
            line-height: 1em;
            border-radius: 100px;
            letter-spacing: 0;
            height: 22px;
            min-width: 22px;
            padding: 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 400;
        }

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

        /* FLATPICKR OVERRIDES */
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected.startRange,
        .flatpickr-day.selected.endRange {
            background: #7a5234;
            border-color: #7a5234;
        }

        /* RESPONSIVE */
        @media (max-width: 370px) {
            .presencecontent { gap: 6px; }
            .iconpresence img { width: 38px; height: 38px; object-fit: cover; }
            .presencetitle { font-size: 14px; }
            .presencedetail span { font-size: 11px; }
        }

        /* PRESENSI CARD — Redesigned (Izin/Lembur/WFH) */
        .presensi-card {
            background: #fff;
            border: 1px solid #f0ece8;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(122,82,52,0.06), 0 4px 12px rgba(0,0,0,0.04);
            padding: 14px 14px 12px;
            transition: box-shadow 0.2s ease, transform 0.15s ease;
        }
        .presensi-card:hover {
            box-shadow: 0 4px 12px rgba(122,82,52,0.10), 0 8px 24px rgba(0,0,0,0.06);
            transform: translateY(-1px);
        }
        .presensi-card:active { transform: translateY(0); }
        .presensi-icon-box {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 1.5px solid;
        }
        .presensi-icon-box i[data-lucide] { width: 22px; height: 22px; stroke-width: 1.75; }
        .icon-izin { background: #fff7ed; border-color: #ffedd5; color: #c0842a; }
        .icon-sakit { background: #fff1f2; border-color: #ffe4e6; color: #e11d48; }
        .icon-lembur { background: #f5f3ff; border-color: #ede9fe; color: #7c3aed; }
        .icon-wfh { background: #f0f9ff; border-color: #e0f2fe; color: #0284c7; }
        .presensi-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.02em;
            padding: 3px 9px; border-radius: 999px; border: 1px solid;
            line-height: 1;
        }
        .badge-izin { background: #fff7ed; border-color: #fed7aa; color: #9a5a1a; }
        .badge-sakit { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
        .badge-lembur { background: #f5f3ff; border-color: #ddd6fe; color: #6d28d9; }
        .badge-wfh { background: #f0f9ff; border-color: #bae6fd; color: #0369a1; }
        .badge-uploaded {
            background: #ecfdf5; border-color: #a7f3d0; color: #065f46;
            font-size: 10px; font-weight: 700; padding: 2px 8px;
        }
        .file-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 600; line-height: 1;
            border: 1.5px solid #e7e5e4;
            background: #fff; color: #44403c;
            cursor: pointer; transition: all 0.15s ease;
            text-decoration: none !important;
        }
        .file-pill:hover {
            border-color: #7a5234; color: #7a5234;
            background: #fdf8f4; transform: translateY(-1px);
        }
        .file-pill:active { transform: translateY(0); }
        .file-pill i[data-lucide] { width: 15px; height: 15px; flex-shrink: 0; }
        .presensi-divider { height: 1px; background: #f5f0eb; margin: 10px 0 10px; }
        .btn-delete-card {
            width: 32px; height: 32px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff1f2; border: 1.5px solid #ffe4e6; color: #e11d48;
            transition: all 0.15s ease; cursor: pointer;
        }
        .btn-delete-card:hover { background: #e11d48; border-color: #e11d48; color: #fff; }
        .btn-delete-card i[data-lucide] { width: 15px; height: 15px; }
        /* FILE PREVIEW MODAL */
        .file-modal-backdrop {
            position: fixed; inset: 0; z-index: 99999;
            background: rgba(20,12,6,0.55); backdrop-filter: blur(6px);
            display: none; align-items: center; justify-content: center;
            padding: 16px;
        }
        .file-modal-backdrop.open { display: flex; }
        .file-modal {
            background: #fff; border-radius: 20px; width: 100%; max-width: 640px;
            max-height: 85vh; display: flex; flex-direction: column;
            overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { opacity:0; transform: scale(0.96) translateY(8px); } to { opacity:1; transform: scale(1) translateY(0); } }
        .file-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid #f0ece8; flex-shrink: 0;
        }
        .file-modal-body { flex: 1; overflow: hidden; background: #fafaf9; display: flex; flex-direction: column; min-height: 320px; }
        .file-modal-body iframe { width: 100%; height: 100%; min-height: 420px; border: 0; background: #fff; }
        .file-modal-body img { width: 100%; height: auto; max-height: 60vh; object-fit: contain; background: #fff; }
        .file-modal-footer {
            display: flex; gap: 8px; padding: 14px 20px; border-top: 1px solid #f0ece8;
            flex-wrap: wrap; justify-content: flex-end; background: #fff;
        }
        .file-modal-footer .btn-modal {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
            border: 1.5px solid; cursor: pointer; text-decoration: none !important; line-height: 1;
        }
        .btn-modal-secondary { background: #fff; border-color: #e7e5e4; color: #44403c; }
        .btn-modal-secondary:hover { border-color: #a8a29e; }
        .btn-modal-primary { background: #7a5234; border-color: #7a5234; color: #fff; }
        .btn-modal-primary:hover { background: #5e3e27; border-color: #5e3e27; }
        .btn-modal-danger { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
        .btn-modal-close {
            width: 36px; height: 36px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #f5f5f4; border: 1px solid #e7e5e4; color: #57534e; cursor: pointer;
        }
        .btn-modal-close:hover { background: #e7e5e4; }

        .flatpickr-calendar {
            background: #ffffff !important;
            border: 1px solid #e6e8e9 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            font-family: inherit !important;
            padding: 8px !important;
            width: 315px !important;
        }

        /* Arrow indicator atas */
        .flatpickr-calendar.arrowTop:before,
        .flatpickr-calendar.arrowTop:after {
            border-bottom-color: #ffffff !important;
        }

        /* Header Bulan & Tahun */
        .flatpickr-current-month {
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            padding-top: 4px !important;
        }

        /* Nama Hari (Sen, Sel, Rab...) */
        span.flatpickr-weekday {
            color: #626976 !important;
            font-weight: 600 !important;
            font-size: 12px !important;
        }

        /* Angka Tanggal */
        .flatpickr-day {
            border-radius: 6px !important;
            color: #1e293b !important;
            font-weight: 500 !important;
        }

        /* Hover Tanggal */
        .flatpickr-day:hover,
        .flatpickr-day:focus {
            background: #f1f5f9 !important;
            border-color: transparent !important;
        }

        /* Tanggal yang Dipilih (Selected) */
        .flatpickr-day.today.selected,
        .flatpickr-day.today.selected:hover,
        .flatpickr-day.today.selected:focus {
            background: #206bc4 !important;
            border-color: #206bc4 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }

        /* Hari Ini (Today) */
        .flatpickr-day.today {
            border-color: #206bc4 !important;
            color: #206bc4 !important;
        }

        .flatpickr-day.today:hover {
            background: #206bc4 !important;
            color: #ffffff !important;
        }

        /* Fix Input Form Alignment di Tabler */
        .flatpickr-input[readonly] {
            background-color: #ffffff !important;
        }
    </style>

</head>

<body style="background-color: #e9ecef;">

    {{-- Header --}}
    @yield('header')

    {{-- App Content --}}
    <div id="appCapsule">

        @yield('content')

    </div>

    {{-- Bottom Navigation --}}
    @include('layouts.bottomNav')

    {{-- File Preview Modal (global) --}}
    <div id="filePreviewBackdrop" class="file-modal-backdrop" aria-hidden="true">
        <div class="file-modal" role="dialog" aria-modal="true">
            <div class="file-modal-header">
                <div class="flex items-center gap-3 min-w-0">
                    <div id="fileModalIcon" class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#fdf8f4] border border-[#f0ece8] text-coklat shrink-0">
                        <i data-lucide="file-text" style="width:18px;height:18px;"></i>
                    </div>
                    <div class="min-w-0">
                        <div id="fileModalTitle" class="text-[14px] font-bold text-[#1a1a1a] leading-tight truncate">Preview Dokumen</div>
                        <div id="fileModalSubtitle" class="text-[11px] text-[#a8a29e] truncate">Memuat...</div>
                    </div>
                </div>
                <button type="button" class="btn-modal-close shrink-0" id="fileModalClose" aria-label="Tutup">
                    <i data-lucide="x" style="width:20px;height:20px;"></i>
                </button>
            </div>
            <div class="file-modal-body" id="fileModalBody">
                <div id="fileModalLoader" class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center">
                    <i data-lucide="hourglass" class="text-[36px] text-[#d6c7b8] animate-pulse" style="width:36px;height:36px;"></i>
                    <p class="text-[13px] text-[#78716c]">Memuat preview...</p>
                </div>
            </div>
            <div class="file-modal-footer">
                <a id="fileModalDownload" href="#" download class="btn-modal btn-modal-secondary" rel="noopener">
                    <i data-lucide="download"></i> Download
                </a>
                <a id="fileModalOpenTab" href="#" target="_blank" rel="noopener" class="btn-modal btn-modal-primary">
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
                const isImage = ['jpg','jpeg','png','webp'].includes(ext);
                const isPdf = ext === 'pdf';
                const isDoc = ['doc','docx'].includes(ext);

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
                    img.onerror = () => { showDocFallback(ext, filename, label); };
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
                const isDoc = ['doc','docx'].includes(ext);
                const title = isDoc ? 'File Word — Preview terbatas' : 'Preview tidak tersedia untuk .' + ext;
                const desc = isDoc
                    ? 'Dokumen <b>' + filename + '</b> berformat <b>.' + ext + '</b> tidak bisa preview langsung di browser. Silakan <b>Download</b> untuk buka di Microsoft Word. <br><br><span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#7a5234] bg-[#fdf8f4] border border-[#f0ece8] rounded-full px-2.5 py-1"><i data-lucide="lightbulb" style="width:14px;height:14px;"></i> Tips: upload sebagai <b>PDF</b> agar bisa preview langsung di sini.</span>'
                    : 'File .' + ext + ' tidak bisa di-preview langsung. Silakan buka di tab baru atau download.';
                body.innerHTML = '<div class="flex flex-col items-center gap-4 p-6 text-center">'
                    + '<div class="w-20 h-20 rounded-2xl bg-[#fdf8f4] border border-[#f0ece8] flex items-center justify-center text-coklat shadow-sm"><i data-lucide="file-text" style="width:36px;height:36px;"></i></div>'
                    + '<div class="w-full max-w-[32ch]">'
                    + '<p class="text-[14px] font-bold text-[#1c1917]">' + title + '</p>'
                    + '<p class="mt-1.5 text-[12px] leading-relaxed text-[#57534e]">' + desc + '</p>'
                    + '<div class="mt-3 inline-flex items-center gap-2 bg-white border border-[#f0ece8] rounded-full px-3 py-1.5 shadow-sm">'
                    + '<i data-lucide="file" class="text-[#a8a29e]" style="width:16px;height:16px;"></i>'
                    + '<span class="text-[11px] font-mono font-semibold text-[#44403c] truncate max-w-[18ch]">' + filename + '</span>'
                    + '<span class="inline-flex items-center justify-center min-w-[28px] h-5 px-1.5 rounded-full bg-[#1c1917] text-white text-[10px] font-bold uppercase">' + ext + '</span>'
                    + '</div>'
                    + '</div></div>';
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
                setTimeout(() => { body.innerHTML = '<div id="fileModalLoader" class="flex flex-1 flex-col items-center justify-center gap-3 p-8 text-center"><i data-lucide="hourglass" class="text-[#d6c7b8] animate-pulse" style="width:36px;height:36px;"></i><p class="text-[13px] text-[#78716c]">Memuat preview...</p></div>'; if(window.lucide)lucide.createIcons(); }, 200);
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
                    const safe = deskripsi.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
                    body.innerHTML = '<div class="p-5">'
                        + '<div class="flex items-center gap-2 mb-3"><div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700"><i data-lucide="clipboard" style="width:18px;height:18px;"></i></div><div><div class="text-[13px] font-bold text-[#1c1917]">' + label + '</div><div class="text-[11px] text-[#78716c]">' + tgl + '</div></div></div>'
                        + '<div class="bg-[#fdf8f4] border border-[#f0ece8] rounded-xl p-4 text-[13px] leading-relaxed text-[#1c1917] whitespace-pre-wrap" style="max-height:50vh; overflow:auto;">' + safe + '</div>'
                        + '</div>';
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
                    if (!win) { window.location.href = url; }
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
            backdrop.addEventListener('click', function(e) { if (e.target === backdrop) closePreview(); });
            document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && backdrop.classList.contains('open')) closePreview(); });
            window.openFilePreview = openPreview;
            window.closeFilePreview = closePreview;
        })();
    </script>

    {{-- Service Worker & Push Subscription --}}
    <script>
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js')
                    .then(function (registration) {
                        console.log('Service Worker Registered');
                        return registration.pushManager.getSubscription();
                    })
                    .then(function (subscription) {
                        if (!subscription) {
                            return Notification.requestPermission().then(function (permission) {
                                if (permission === 'granted') {
                                    return registerPush();
                                }
                            });
                        }
                    })
                    .catch(function (error) {
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
                    applicationServerKey: urlBase64ToUint8Array('{{ config("webpush.vapid.public_key") }}')
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
