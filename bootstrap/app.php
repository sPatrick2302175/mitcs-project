<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException; // Don't forget to import this!
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Your custom middleware can be registered here if needed later
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        $exceptions->render(function (AuthorizationException $e, Request $request) {

            return redirect()->route('dashboard');
        });
    })->create();