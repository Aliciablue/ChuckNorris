<div class="d-flex justify-content-end mb-3">
    <div>
        <a href="{{ route('language.switch', ['locale' => 'en']) }}" class="me-2">
            <span class="{{ app()->getLocale() === 'en' ? 'fw-bold' : '' }}">EN</span>
            {{-- Optional: You can add a small flag icon here if you have one --}}
        </a>
        <a href="{{ route('language.switch', ['locale' => 'es']) }}">
            <span class="{{ app()->getLocale() === 'es' ? 'fw-bold' : '' }}">ES</span>
            {{-- Optional: You can add a small flag icon here if you have one --}}
        </a>
    </div>
</div>