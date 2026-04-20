<?php

use App\Modules\Students\Http\Middleware\EnsureStudentCanAccessPortal;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(
            guests: function (Request $request): string {
                if ($request->is('student') || $request->is('student/*')) {
                    return route('student.login');
                }

                if ($request->is('admin') || $request->is('admin/*')) {
                    return route('admin.login');
                }

                return route('welcome');
            },
            users: function (Request $request): string {
                if ($request->is('student') || $request->is('student/*')) {
                    return route('student.dashboard');
                }

                if ($request->is('admin') || $request->is('admin/*')) {
                    return route('admin.dashboard');
                }

                return route('welcome');
            },
        );

        $middleware->alias([
            'student.portal' => EnsureStudentCanAccessPortal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
