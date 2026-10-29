<?php

use App\Http\Middleware\EnsureUserRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);
                // Public invitation forms are authorised by the secret link, not by a login
        // cookie, so there is no session for CSRF to protect. Excluding them avoids
        // "page expired" (419) errors for phones that reopen an old tab.
        $middleware->validateCsrfTokens(except: ['join/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
