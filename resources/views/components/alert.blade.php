{{-- resources/views/components/alert.blade.php --}}
@props(['type' => 'info'])

@php
    $class = match ($type) {
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'success' => 'alert-success',
        default => 'alert-info',
    };
@endphp

<div class="alert {{ $class }}">
    {{ $slot }}
</div>