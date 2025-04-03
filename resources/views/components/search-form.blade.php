{{-- resources/views/components/search-form.blade.php --}}
<form action="{{ route('search.results', ['locale' => app()->getLocale()]) }}" method="GET"  id="searchForm" data-old-type="{{ old('type', 'keyword') }}"  data-old-query="{{ old('query') }}">
    <div id="frontend-validation-errors" class="alert alert-danger" style="display:none;">
        <ul></ul>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('index.search') }}</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="keyword" value="keyword"
                    {{ old('type', 'keyword') == 'keyword' ? 'checked' : '' }}>
                <label class="form-check-label" for="keyword" data-keyword-label="{{ __('index.keyword_label') }}"
                    data-keyword-helper="{{ __('index.keyword_helper') }}"
                    data-keyword-placeholder="{{ __('index.keyword_placeholder') }}">{{ __('index.words') }}</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="category" value="category"
                    {{ old('type') == 'category' ? 'checked' : '' }}>
                <label class="form-check-label" for="category" data-category-label="{{ __('index.category') }}:"
                    data-category-helper="{{ __('index.categorySelect') }}">{{ __('index.category') }}</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="random" value="random"
                    {{ old('type') == 'random' ? 'checked' : '' }}>
                <label class="form-check-label" for="random">{{ __('index.random') }}</label>
            </div>
            <div id="search-type-error"></div> 

        </div>
    </div>

    <div class="mb-3" id="query-container">
        <label for="query" class="form-label" id="query-label"></label>
        <input type="text" class="form-control" id="query" name="query" value="{{ old('query') }}"  maxlength="255">
        <small class="form-text text-muted" id="query-helper"></small>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">{{ __('index.send_email_optional') }}</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
    </div>

    <button type="submit" class="btn btn-primary">{{ __('index.search_button') }}</button>
</form>

<script>
    // Frontend Validation ... (your existing script - ensure it's still correctly targeting elements)
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])