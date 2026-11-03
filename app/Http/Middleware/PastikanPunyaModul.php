<?php

namespace App\Http\Middleware;

use App\Enums\Modul;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('modul:berita'). Izin ditegakkan di server lewat Gate `akses-modul`.
 */
class PastikanPunyaModul
{
    public function handle(Request $request, Closure $next, string $modul): Response
    {
        $modul = Modul::tryFrom($modul) ?? throw new \InvalidArgumentException("Modul tidak dikenal: {$modul}");

        abort_unless(Gate::allows('akses-modul', $modul), 403);

        return $next($request);
    }
}
