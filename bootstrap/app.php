<?php

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
        $middleware->prepend(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->trustHosts(at: fn () => array_map(
            fn ($host) => '^'.preg_quote($host, '/').'$',
            config('security.trusted_hosts'),
        ), subdomains: false);
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\AdminAccess::class,
        );
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: array_map('trim', explode(',', $proxies)));
        }
        $middleware->alias([
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
            'superadmin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(fn ($response) => app(\App\Http\Middleware\SecurityHeaders::class)->secure($response, request()));
    })->create();
