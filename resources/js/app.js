import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { createIcons, icons } from 'lucide';
import Swal from 'sweetalert2';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import 'flatpickr/dist/flatpickr.min.css';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.Swal = Swal;
window.flatpickr = flatpickr;
window.L = L;
window.lucide = { createIcons: () => createIcons({ icons }) };

flatpickr.l10ns.id = Indonesian;
if (flatpickr.defaults) {
    flatpickr.defaults.locale = 'id';
}

createIcons({ icons });
Alpine.start();
