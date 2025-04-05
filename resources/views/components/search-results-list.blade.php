{{-- resources/views/components/search/search-results-list.blade.php --}}
@props(['results'])

@if (count($results) > 0)
<ul class="list-unstyled">
    @foreach ($results as $result)
        <li class="border rounded p-3 mb-2 fact-item">
            <span class="fact-text">{{ $result['value'] ?? ($result['joke'] ?? __('results.no_results_singular')) }}</span>
            <button class="btn btn-sm btn-outline-secondary copy-button" data-fact="{{ $result['value'] ?? ($result['joke'] ?? __('results.no_results_singular')) }}">
                {{ __('messages.copy') }}
            </button>
        </li>
    @endforeach
</ul>
@else
    <p>{{ __('results.no_results') }}</p>
@endif
