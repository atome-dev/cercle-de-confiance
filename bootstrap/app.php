<?php

use App\Http\Middleware\EnsureAccessCodeIsValid;
use App\Http\Middleware\EnsureAdminHasTwoFactor;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'access.code' => EnsureAccessCodeIsValid::class,
            'role' => RoleMiddleware::class,
            'ensure2fa' => EnsureAdminHasTwoFactor::class,
        ]);

        // Guests must hit the shared access gate before authentication is
        // enforced, so that routes combining 'access.code' and 'auth'
        // redirect to /acces instead of /login.
        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: EnsureAccessCodeIsValid::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
