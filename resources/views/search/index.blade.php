{{-- resources/views/index.blade.php --}}
@extends('layouts.app')

@section('title', __('index.titleIndex'))

@section('content')
    <h1>{{ __('index.titleIndex') }}</h1>

    @if ($errors->any())
        <x-alert type="danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-search-form />
@endsection