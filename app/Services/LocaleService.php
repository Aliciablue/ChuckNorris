<?php
namespace App\Services;

use Illuminate\Support\Facades\App;

class LocaleService
{
    public function setLocale(string $locale): void
    {
        App::setLocale($locale);
    }
}
