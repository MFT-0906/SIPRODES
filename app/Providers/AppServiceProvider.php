<?php

namespace App\Providers;

use App\Support\DemoContent;
use App\Support\DemoStore;
use App\Support\Navigation;
use App\Support\Peran;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
            // TODO(backend): $pengguna dan $aspirasiBaru berasal dari pengguna yang masuk dan database.
            $pengguna = DemoContent::admin()['pengguna'];
            $pengguna['name'] = auth()->user()?->name ?? $pengguna['name'];
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
