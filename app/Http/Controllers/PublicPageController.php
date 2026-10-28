<?php

namespace App\Http\Controllers;

use App\Support\DemoContent;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    // TODO(backend): ganti DemoContent dengan data dari database.

    public function home(): View
    {
        return view('pages.public.beranda', [
            'content' => DemoContent::beranda(),
            'statistik' => DemoContent::statistik(),
            'identitas' => DemoContent::identitas(),
        ]);
    }

    public function profil(): View
    {
        return view('pages.public.profil', [
            'content' => DemoContent::profil(),
            'identitas' => DemoContent::identitas(),
        ]);
    }
}
