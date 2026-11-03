<?php

use App\Http\Middleware\HeaderKeamanan;
use App\Http\Middleware\PastikanPunyaModul;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['modul' => PastikanPunyaModul::class]);
        // Izin modul dicek SEBELUM model di-bind ke rute, supaya akun tanpa izin selalu mendapat 403
        // (bukan 404 yang membocorkan ada/tidaknya data).
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: PastikanPunyaModul::class);
        // Global (bukan hanya grup web) agar halaman 404 untuk rute yang tidak ada pun mendapat header.
        $middleware->append(HeaderKeamanan::class);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
