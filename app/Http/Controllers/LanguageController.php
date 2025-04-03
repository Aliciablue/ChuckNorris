<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale, Request $request)
    {
        Log::info('Switching language from ' . app()->getLocale() . ' to: ' . $locale);
        if (in_array($locale, ['en', 'es'])) {
            App::setLocale($locale);
            Session::put('locale', $locale); // Store the locale in the session
        }
        Log::info('Language set to ' . app()->getLocale());
        Log::info('Session Language set to ' . Session::get('locale'));
        return redirect()->to('/' . $locale); // Redirect to the homepage with the new locale prefix

    }
}
