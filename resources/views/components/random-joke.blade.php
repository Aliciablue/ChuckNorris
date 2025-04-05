{{-- resources/views/components/random-joke.blade.php --}}
{{-- This component displays a random joke if it is provided. --}}

{{-- Check if the random joke is set and not empty --}}
@if (isset($randomJoke))
    <p style="font-style: italic; color: #777; margin-bottom: 15px; text-align:center;">
        {{ $randomJoke }}
    </p>
@endif