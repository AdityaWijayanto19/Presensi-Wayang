@props(['type' => 'error'])

@php
$colors = [
    'success' => 'bg-green-50 border-green-200 text-green-700',
    'error'   => 'bg-red-50 border-red-200 text-red-700',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
    'info'    => 'bg-blue-50 border-blue-200 text-blue-700',
];
@endphp

<div class="{{ $colors[$type] }} border px-4 py-3 rounded-lg text-sm" role="alert">
    {{ $slot }}
</div>
