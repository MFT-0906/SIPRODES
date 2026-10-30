<?php

namespace App\Http\Controllers;

use App\Support\Navigation;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    // TODO(backend): middleware `auth` dan pengecekan izin per peran dipasang oleh backend.

    /** Halaman kerangka admin; isi tiap modul dibuat pada fase 9–10. */
    public function halaman(): View
    {
        foreach (Navigation::admin() as $grup) {
            foreach ($grup['items'] as $item) {
                if (request()->routeIs(...$item['match'])) {
                    return view('pages.admin.stub', ['judul' => $item['label']]);
                }
            }
        }

        abort(404);
    }
}
