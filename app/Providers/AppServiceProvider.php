<?php

namespace App\Providers;

use App\Support\DemoContent;
use App\Support\DemoStore;
use App\Support\Navigation;
use App\Support\Peran;
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
            $pengguna = DemoContent::admin()['pengguna'];
            $pengguna['role'] = Peran::saatIni();

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
                'aspirasiBaru' => collect(DemoStore::daftar('aspirasi'))->where('status', 'baru')->count(),
            ]);
        });
    }
}
