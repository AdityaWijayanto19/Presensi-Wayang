@props(['class' => ''])

<div class="p-3">
    <div class="max-w-7xl mx-auto {{ $class }}">
        {{ $slot }}
    </div>
</div>
