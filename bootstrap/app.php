<?php

use App\Exceptions\GuestOnlyRouteException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('api')
                ->prefix('api')
                ->group(function () {
                    foreach (['v1'] as $version) {
                        Route::prefix($version)->group(function () use ($version) {
                            Route::group([], base_path("routes/api/{$version}/api.php"));

                            Route::prefix('admin')
                                ->group(base_path("routes/api/{$version}/admin.php"));
                        });
                    }
                });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->expectsJson()) {
                throw new GuestOnlyRouteException();
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
