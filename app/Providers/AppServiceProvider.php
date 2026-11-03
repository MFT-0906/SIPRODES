<?php

namespace App\Providers;

use App\Enums\Modul;
use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\User;
use App\Support\KontenPublik;
use App\Support\Navigation;
use App\Support\Peran;
use App\Support\Produksi;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu instance per permintaan agar profil desa dibaca sekali.
        $this->app->scoped(KontenPublik::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Produksi::terapkan($this->app);

        // Pembatasan pengiriman aspirasi per IP (disimpan sebagai hash di cache, bukan di database): 5 per 10 menit, 30 per hari.
        RateLimiter::for('aspirasi', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by('aspirasi:'.sha1((string) $request->ip())),
            Limit::perDay(30)->by('aspirasi-harian:'.sha1((string) $request->ip())),
        ]);

        Gate::define('akses-modul', fn (User $user, Modul $modul) => $user->aktif && $user->punyaModul($modul));

        ResetPassword::createUrlUsing(fn (object $notifiable, string $token) => route('admin.password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $menit = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
            $url = route('admin.password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('Atur ulang kata sandi — '.config('app.name'))
                ->greeting('Halo,')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun admin Anda.')
                ->action('Buat kata sandi baru', $url)
                ->line("Tautan ini berlaku selama {$menit} menit.")
                ->line('Jika Anda tidak meminta pengaturan ulang, abaikan email ini; kata sandi Anda tidak berubah.')
                ->salutation('Salam, '.config('app.name'));
        });

        View::composer(['layouts.admin', 'pages.admin.dashboard'], function ($view) {
            $pengguna = ['name' => auth()->user()?->name ?? '', 'role' => Peran::saatIni()];
            $superAdmin = $pengguna['role'] === 'super_admin';

            // Penyaring tampilan menurut izin modul; izin sebenarnya ditegakkan middleware `modul:*`.
            $menu = collect(Navigation::admin())
                ->map(function (array $grup) {
                    $grup['items'] = array_values(array_filter(
                        $grup['items'],
                        fn (array $item) => ! isset($item['modul']) || Gate::allows('akses-modul', Modul::from($item['modul'])),
                    ));

                    return $grup;
                })
                ->filter(fn (array $grup) => $grup['items'] !== [])
                ->values()
                ->all();

            $view->with([
                'pengguna' => $pengguna,
                'peranLabel' => $superAdmin ? 'Super Admin' : 'Staf',
                'menuAdmin' => $menu,
                'aspirasiBaru' => Gate::allows('akses-modul', Modul::Aspirasi) ? Aspirasi::query()->where('status', StatusAspirasi::Baru)->count() : 0,
            ]);
        });
    }
}
