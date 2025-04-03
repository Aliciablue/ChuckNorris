<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LanguageController;

Route::get('/', function () {
    return Redirect::to('/en'); // Redirect to the default English version
});

Route::group(['prefix' => '{locale}', 'middleware' => 'locale'], function () {
    Route::get('/', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search', [SearchController::class, 'search'])->name('search.results');
    Route::get('/categories', [SearchController::class, 'categories'])->name('search.categories');
});
Route::get('/language/switch/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
