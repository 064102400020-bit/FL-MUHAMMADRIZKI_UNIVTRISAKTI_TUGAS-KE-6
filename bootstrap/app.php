<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrasi alias middleware kustom
        $middleware->alias([
            'admin'    => \App\Http\Middleware\IsAdmin::class,
            'customer' => \App\Http\Middleware\IsCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Pastikan ValidationException selalu mengembalikan JSON
         * saat request berasal dari API (bukan redirect HTML).
         * Mengembalikan HTTP 422 Unprocessable Content.
         */
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal. Periksa kembali data yang dikirim.',
                    'data'    => $e->errors(),
                ], 422);
            }
        });

        /*
         * Pastikan AuthenticationException selalu mengembalikan JSON
         * saat request berasal dari API dan pengguna belum terautentikasi (tanpa token).
         * Mencegah crash error 500 Route [login] not defined bila lupa header Accept: application/json.
         * Mengembalikan HTTP 401 Unauthorized.
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401);
            }
        });
    })->create();
