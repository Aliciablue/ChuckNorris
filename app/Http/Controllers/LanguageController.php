<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function __construct(protected LoggerInterface $logger) {}
    public function switch($locale, Request $request)
    {
        $this->logger->info('Switching language from ' . app()->getLocale() . ' to: ' . $locale);
        if (in_array($locale, ['en', 'es'])) {
            App::setLocale($locale);
            Session::put('locale', $locale); // Store the locale in the session
        }
        $this->logger->info('Language set to ' . app()->getLocale());
        $this->logger->info('Session Language set to ' . Session::get('locale'));
        return redirect()->to('/' . $locale); // Redirect to the homepage with the new locale prefix

    }
}
