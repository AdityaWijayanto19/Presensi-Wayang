<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>WAG Presensi Digital - Administrator</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/img/login/logo_aplikasi.png') }}" sizes="32x32">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    <div class="flex min-h-screen">

        @include('layouts.admin.sidebar')

        <div class="flex-1 flex flex-col lg:pl-56">

            @include('layouts.admin.header')

            <main class="flex-1">
                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <script>
    function searchableSelect() {
        return {
            open: false,
            search: '',
            selectedValue: '',
            selectedLabel: '',
            options: [],
            filtered: [],
            highlightedIndex: -1,

            init() {
                var self = this;
                var select = self.$refs.nativeSelect;
                if (select) {
                    Array.from(select.options).forEach(function(opt) {
                        if (opt.value !== '') {
                            self.options.push({ value: opt.value, label: opt.textContent.trim() });
                        }
                    });
                    self.filtered = self.options.slice();
                    if (select.value) {
                        self.selectedValue = select.value;
                        var found = self.options.find(function(o) { return o.value === select.value; });
                        if (found) self.selectedLabel = found.label;
                    }
                }
                self.$watch('search', function() {
                    var q = self.search.toLowerCase();
                    self.filtered = q
                        ? self.options.filter(function(o) { return o.label.toLowerCase().indexOf(q) !== -1; })
                        : self.options.slice();
                    self.highlightedIndex = -1;
                    self.$nextTick(function() {
                        if (window.lucide) lucide.createIcons();
                    });
                });
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.search = '';
                    this.filtered = this.options.slice();
                    this.highlightedIndex = -1;
                    var self = this;
                    this.$nextTick(function() {
                        if (self.$refs.searchInput) self.$refs.searchInput.focus();
                        if (window.lucide) lucide.createIcons();
                    });
                }
            },

            close() {
                this.open = false;
                this.search = '';
            },

            select(option) {
                this.selectedValue = option.value;
                this.selectedLabel = option.label;
                var select = this.$refs.nativeSelect;
                if (select) {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                this.close();
            },

            handleKeydown(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.filtered.length - 1);
                    this.scrollToHighlighted();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                    this.scrollToHighlighted();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.highlightedIndex >= 0 && this.filtered[this.highlightedIndex]) {
                        this.select(this.filtered[this.highlightedIndex]);
                    }
                }
            },

            scrollToHighlighted() {
                var self = this;
                self.$nextTick(function() {
                    var list = self.$refs.optionsList;
                    if (!list) return;
                    var highlighted = list.querySelector('.bg-blue-50');
                    if (highlighted) highlighted.scrollIntoView({ block: 'nearest' });
                });
            },

            refreshOptions() {
                var self = this;
                var select = self.$refs.nativeSelect;
                if (!select) return;
                self.options = [];
                Array.from(select.options).forEach(function(opt) {
                    if (opt.value !== '') {
                        self.options.push({ value: opt.value, label: opt.textContent.trim() });
                    }
                });
                var q = self.search.toLowerCase();
                self.filtered = q
                    ? self.options.filter(function(o) { return o.label.toLowerCase().indexOf(q) !== -1; })
                    : self.options.slice();
                self.selectedValue = select.value;
                var found = self.options.find(function(o) { return o.value === select.value; });
                self.selectedLabel = found ? found.label : '';
                self.$nextTick(function() {
                    if (window.lucide) lucide.createIcons();
                });
            }
        };
    }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutBtn = document.getElementById('logout-admin');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var url = this.getAttribute('href');
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
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            }
        });
    </script>

    <x-admin.alert />

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
                    var badgeLembur = document.getElementById('adminLemburBadge');
                    var totalLembur = (data.pending_lembur || 0) + (data.pending_laporan_lembur || 0);
                    if(badgeLembur){
                        if(totalLembur > 0){
                            badgeLembur.textContent = totalLembur;
                            badgeLembur.style.display = 'inline-flex';
                        } else {
                            badgeLembur.style.display = 'none';
                        }
                    }
                    var badgeIzin = document.getElementById('adminIzinBadge');
                    var totalIzin = data.pending_izin || 0;
                    if(badgeIzin){
                        if(totalIzin > 0){
                            badgeIzin.textContent = totalIzin;
                            badgeIzin.style.display = 'inline-flex';
                        } else {
                            badgeIzin.style.display = 'none';
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
