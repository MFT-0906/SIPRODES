<?php

namespace App\Providers;

use App\Support\DemoContent;
use App\Support\Navigation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.admin', 'pages.admin.dashboard'], function ($view) {
            // TODO(backend): $pengguna dan $aspirasiBaru berasal dari pengguna yang masuk dan database.
            $demo = DemoContent::admin();
            $pengguna = $demo['pengguna'];

            // Hanya di lokal: ?peran=super_admin|staf untuk meninjau menu tiap peran (tersimpan di sesi).
            if (app()->environment('local')) {
                $peran = request()->query('peran');
                if (in_array($peran, ['super_admin', 'staf'], true)) {
                    session(['demo_peran' => $peran]);
                }
                $pengguna['role'] = session('demo_peran', $pengguna['role']);
            }

            // Hanya penyaring tampilan; izin sebenarnya ditegakkan backend.
            $superAdmin = $pengguna['role'] === 'super_admin';
            $menu = collect(Navigation::admin())
                ->reject(fn (array $grup) => ($grup['super_admin'] ?? false) && ! $superAdmin)
                ->values()
                ->all();

            $view->with([
                'pengguna' => $pengguna,
                'peranLabel' => $superAdmin ? 'Super Admin' : 'Staf',
                'menuAdmin' => $menu,
                'aspirasiBaru' => $demo['aspirasi_baru'],
            ]);
        });
    }
}
