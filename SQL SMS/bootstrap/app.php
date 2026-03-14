<?php

use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\RequireAdmin;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\RequireDealer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [CorsMiddleware::class]);
        $middleware->alias([
            'auth.sms' => RequireAuth::class,
            'admin' => RequireAdmin::class,
            'dealer' => RequireDealer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
