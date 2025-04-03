{{-- resources/views/components/back-button.blade.php --}}
@props(['route', 'label'])

<a href="{{ $route }}" class="btn btn-primary">{{ $label }}</a>