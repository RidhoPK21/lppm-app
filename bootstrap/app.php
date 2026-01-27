<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // 1. WAJIB: Jalankan otomatis di setiap request Web (agar Sidebar muncul)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // 2. WAJIB: Tetap daftarkan alias (agar error "Target class does not exist" hilang)
        $middleware->alias([
            'api.check.auth' => \App\Http\Middleware\ApiCheckTokenMiddleware::class,
            'check.auth' => \App\Http\Middleware\CheckAuthMiddleware::class,
            // Baris ini JANGAN DIHAPUS jika di route Anda ada ->middleware('handle.inertia')
            'handle.inertia' => \App\Http\Middleware\HandleInertiaRequests::class, 
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Exception handling untuk API (Biarkan seperti semula)
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                if ($e instanceof HttpResponseException) return null;
                if ($e instanceof ThrottleRequestsException) return null;

                $status = 500;
                if (method_exists($e, 'getCode') && is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) {
                    $status = $e->getCode();
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ] : null,
                ], $status);
            }
        });
    })
    ->create();