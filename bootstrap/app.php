<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
           //SetLocale::class,//Set after the StartSession middleware!
          LocaleMiddleware::class,
        ]);
        $middleware->alias([
            'locale' => LocaleMiddleware::class,
            
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
