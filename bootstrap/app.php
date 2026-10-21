<?php

<<<<<<< HEAD
=======
use App\Http\Middleware\EnsureUserRole;
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
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
<<<<<<< HEAD
        //
=======
        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
