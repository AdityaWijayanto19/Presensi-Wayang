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
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});
L.Icon.Default.imagePath = '';

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

// Capture beforeinstallprompt globally (event can fire before /install is opened)
window.__deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    window.__deferredInstallPrompt = e;
});

Alpine.start();
