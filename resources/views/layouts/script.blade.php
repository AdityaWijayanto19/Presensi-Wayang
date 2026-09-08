{{-- jQuery --}}
<script src="{{ asset('assets/js/lib/jquery-3.4.1.min.js') }}"></script>

{{-- Bootstrap --}}
<script src="{{ asset('assets/js/lib/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/lib/bootstrap.min.js') }}"></script>

{{-- Lucide Icons --}}
<script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.min.js"></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide)lucide.createIcons();});</script>

{{-- Owl Carousel --}}
<script src="{{ asset('assets/js/plugins/owl-carousel/owl.carousel.min.js') }}"></script>

{{-- jQuery Circle Progress --}}
<script src="{{ asset('assets/js/plugins/jquery-circle-progress/circle-progress.min.js') }}"></script>

{{-- Webcam.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Flatpickr JS & Indonesian Locale -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

{{-- Base JS --}}
<script src="{{ asset('assets/js/base.js') }}"></script>

{{-- Custom Script --}}
@stack('myscript')
