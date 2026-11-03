<?php

use App\Http\Middleware\HeaderKeamanan;
use App\Http\Middleware\PastikanPunyaModul;
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
        $middleware->alias(['modul' => PastikanPunyaModul::class]);
        // Global (bukan hanya grup web) agar halaman 404 untuk rute yang tidak ada pun mendapat header.
        $middleware->append(HeaderKeamanan::class);

        // Di belakang proxy/penyeimbang beban (mis. hosting dengan HTTPS di proxy), isi TRUSTED_PROXIES di .env
        // (daftar IP dipisah koma, atau * bila proxy tidak dapat diketahui) agar HTTPS dan IP pengunjung terbaca benar.
        if ($proksi = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proksi === '*' ? '*' : array_map('trim', explode(',', $proksi)));
        }
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
