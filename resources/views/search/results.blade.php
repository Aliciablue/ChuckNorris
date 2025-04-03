{{-- resources/views/results.blade.php --}}
@extends('layouts.app')

@section('title', __('results.search_results'))

@section('content')
    <h1>{{ __('results.search_results') }}</h1>
   
    <x-success-message/>

    @if ($searchType === 'keyword' && $searchTerm)
        <p>{{ __('results.keyword_search') }} <strong>{{ $searchTerm }}</strong></p>
    @elseif ($searchType === 'category' && $searchTerm)
        <p>{{ __('results.category_search') }} <strong>{{ $searchTerm }}</strong></p>
    @elseif ($searchType === 'random')
        <p>{{ __('results.random_search') }}</p>
    @endif

    <x-search-results-list :results="$results" />

    @if (method_exists($results, 'links'))
        <div class="mt-4">
            {{ $results->links() }}
        </div>
    @endif

    <x-back-button :route="route('search.index', ['locale' => app()->getLocale()])" :label="__('results.back_to_search')" />
@endsection
