@php
    $alerts = [];
    if (Session::has('success')) $alerts[] = ['type' => 'success', 'message' => Session::pull('success')];
    if (Session::has('warning')) $alerts[] = ['type' => 'warning', 'message' => Session::pull('warning')];
    if (Session::has('error'))   $alerts[] = ['type' => 'error',   'message' => Session::pull('error')];
    if (Session::has('info'))    $alerts[] = ['type' => 'info',    'message' => Session::pull('info')];
@endphp

<div x-data="alertToast()" x-init="init({{ json_encode($alerts) }})"
     x-on:add-alert.window="add($event.detail.type, $event.detail.message)"
     class="fixed top-6 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center gap-2 w-full max-w-lg px-4 pointer-events-none"
     style="{{ count($alerts) === 0 ? 'display:none;' : '' }}">

    <template x-for="alert in alerts" :key="alert.id">
        <div x-show="alert.show" x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-48"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-48"
             class="pointer-events-auto rounded-md shadow-lg flex items-start gap-2.5 p-3 pr-2 w-full"
             :class="{
                 'bg-emerald-50 border border-emerald-200': alert.type === 'success',
                 'bg-amber-50 border border-amber-200': alert.type === 'warning',
                 'bg-red-50 border border-red-200': alert.type === 'error',
                 'bg-blue-50 border border-blue-200': alert.type === 'info'
             }">

            <template x-if="alert.type === 'success'">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
            </template>
            <template x-if="alert.type === 'warning'">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
            </template>
            <template x-if="alert.type === 'error'">
                <i data-lucide="x-circle" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
            </template>
            <template x-if="alert.type === 'info'">
                <i data-lucide="info" class="w-4 h-4 text-blue-500 mt-0.5 shrink-0"></i>
            </template>

            <p x-text="alert.message" class="text-sm flex-1 min-w-0 leading-snug"
               :class="{
                   'text-emerald-700': alert.type === 'success',
                   'text-amber-700': alert.type === 'warning',
                   'text-red-700': alert.type === 'error',
                   'text-blue-700': alert.type === 'info'
               }"></p>

            <button @click="dismiss(alert.id)"
                class="shrink-0 mt-0.5 transition-colors"
                :class="{
                    'text-emerald-300 hover:text-emerald-500': alert.type === 'success',
                    'text-amber-300 hover:text-amber-500': alert.type === 'warning',
                    'text-red-300 hover:text-red-500': alert.type === 'error',
                    'text-blue-300 hover:text-blue-500': alert.type === 'info'
                }">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </template>
</div>

<script>
function alertToast() {
    return {
        alerts: [],
        _nextId: 1,
        _timerIds: [],
        init(items) {
            var self = this;
            if (items && items.length) {
                self.$el.style.display = '';
                items.forEach(function(item) { self.add(item.type, item.message); });
            }
        },
        add(type, message) {
            var id = this._nextId++;
            var self = this;
            self.$el.style.display = '';
            self.alerts.push({ id: id, type: type, message: message, show: false });
            self.$nextTick(function() {
                var a = self.alerts.find(function(x) { return x.id === id; });
                if (a) a.show = true;
                if (window.lucide) lucide.createIcons();
            });
            var tid = setTimeout(function() { self.dismiss(id); }, 5000);
            self._timerIds.push(tid);
        },
        dismiss(id) {
            var self = this;
            var alert = self.alerts.find(function(a) { return a.id === id; });
            if (alert) alert.show = false;
            setTimeout(function() {
                self.alerts = self.alerts.filter(function(a) { return a.id !== id; });
                if (self.alerts.length === 0 && self.$el) self.$el.style.display = 'none';
            }, 300);
        }
    };
}
window.showToast = function(type, message) {
    window.dispatchEvent(new CustomEvent('add-alert', { detail: { type: type, message: message } }));
};
</script>
