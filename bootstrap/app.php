<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                $message = 'Sesi Anda telah berakhir. Muat ulang halaman ini lalu coba lagi.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 419);
                }

                return redirect()->back()->with('error', $message)->withInput();
            }

            if ($e->getStatusCode() !== 403) {
                return null;
            }

            $message = $e->getMessage() ?: null;

            if (!$message && $route = $request->route()) {
                foreach ($route->gatherMiddleware() as $middleware) {
                    if (str_starts_with($middleware, 'role:')) {
                        $roles = substr($middleware, 5);
                        $message = match ($roles) {
                            'buyer' => 'Fitur ini khusus untuk akun pembeli (buyer). Admin tidak dapat melakukan pemesanan.',
                            'owner|admin' => 'Fitur ini khusus untuk Owner dan Admin.',
                            'owner|admin|penulis' => 'Area ini khusus untuk pegawai toko (Owner, Admin, atau Penulis).',
                            default => "Fitur ini khusus untuk role: {$roles}.",
                        };
                    } elseif (str_starts_with($middleware, 'permission:')) {
                        $permission = substr($middleware, 11);
                        $message = "Anda tidak memiliki izin mengakses fitur ini. (Permission: {$permission})";
                    }
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
                ], 403);
            }

            return response()->view('errors.403', [
                'message' => $message ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
            ], 403)->header('Content-Type', 'text/html');
        });
    })->create();
