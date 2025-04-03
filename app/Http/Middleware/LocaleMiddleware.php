<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    protected $locales = ['en', 'es']; // Add all your supported locales

    public function handle($request, Closure $next)
    {
        $localeFromUrl = $request->segment(1); // Get the first segment of the URI

        if ($localeFromUrl && in_array($localeFromUrl, $this->locales)) {
            App::setLocale($localeFromUrl);
            Session::put('locale', $localeFromUrl); // Store in session
        } elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale')); // Set from session if available
        } else {
            App::setLocale(config('app.fallback_locale')); // Set default fallback locale
        }

        return $next($request);
    }
}