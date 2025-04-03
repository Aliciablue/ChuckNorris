{{-- resources/views/components/search/search-results-list.blade.php --}}
@props(['results'])

@if (count($results) > 0)
    <ul class="list-unstyled">
        @foreach ($results as $result)
            <li class="border rounded p-3 mb-2">
                {{ $result['value'] ?? ($result['joke'] ?? __('results.no_results_singular')) }}</li>
        @endforeach
    </ul>
@else
    <p>{{ __('results.no_results') }}</p>
@endif