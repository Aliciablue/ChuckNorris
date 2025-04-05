<div class="d-flex justify-end mb-3">
    <div class="relative">
        <button class="btn btn-primary py-2 px-4 rounded-md focus:outline-none focus:shadow-outline dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            {{ strtoupper(app()->getLocale()) }}
        </button>
        <ul class="dropdown-menu absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
            <li>
                <a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === 'en' ? 'font-semibold' : '' }}" href="{{ route('language.switch', ['locale' => 'en']) }}">
                    EN
                    @if (app()->getLocale() === 'en')
                        <i class="bi bi-check-lg ms-2"></i>
                    @endif
                </a>
            </li>
            <li>
                <a class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === 'es' ? 'font-semibold' : '' }}" href="{{ route('language.switch', ['locale' => 'es']) }}">
                    ES
                    @if (app()->getLocale() === 'es')
                        <i class="bi bi-check-lg ms-2"></i>
                    @endif
                </a>
            </li>
        </ul>
    </div>
</div>