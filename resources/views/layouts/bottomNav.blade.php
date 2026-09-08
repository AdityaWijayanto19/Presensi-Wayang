<div class="appBottomMenu">

    <a href="/dashboard"
        class="item {{ request()->is('dashboard') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="home"></i>
            <strong>Home</strong>
        </div>
    </a>

    <a href="/presensi"
        class="item {{ request()->is('presensi') && !request()->is('presensi/create') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="clock"></i>
            <strong>Histori</strong>
        </div>
    </a>

    <a href="/presensi/create"
        class="item {{ request()->is('presensi/create') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="camera"></i>
            <strong>Presensi</strong>
        </div>
    </a>

    <a href="/izin"
        class="item {{ request()->is('izin') || request()->is('izin/create') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="file-text"></i>
            <strong>Izin</strong>
        </div>
    </a>

    <a href="/lembur"
        class="item {{ request()->is('lembur') || request()->is('lembur/create') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="timer"></i>
            <strong>Lembur</strong>
        </div>
    </a>

    <a href="/wfh"
        class="item {{ request()->is('wfh') || request()->is('wfh/create') || request()->is('wfh/*/laporan') ? 'active disabled' : '' }}">
        <div class="col">
            <i data-lucide="save"></i>
            <strong>WFH</strong>
        </div>
    </a>

</div>
