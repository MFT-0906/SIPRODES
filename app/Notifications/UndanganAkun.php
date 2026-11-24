<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\TautanUndangan;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email undangan untuk akun admin baru. Tidak memuat kata sandi apa pun: hanya tautan untuk membuatnya sendiri,
 * dengan masa berlaku broker "undangan" (bawaan 72 jam), lebih panjang daripada tautan lupa kata sandi.
 */
class UndanganAkun extends Notification
{
    public function __construct(public readonly string $token) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $jam = TautanUndangan::masaBerlakuJam();

        return (new MailMessage)
            ->subject('Akun Anda di Website '.config('desa.wilayah.sebutan').' '.config('desa.wilayah.nama'))
            ->greeting("Halo {$notifiable->name},")
            ->line("Super Admin telah membuatkan akun admin untuk Anda dengan peran {$notifiable->peran->label()}.")
            ->action('Buat kata sandi', TautanUndangan::url($notifiable->email, $this->token))
            ->line("Tautan ini berlaku selama {$jam} jam. Setelah itu, gunakan \"Lupa kata sandi?\" di halaman masuk.")
            ->line('Bila email ini didaftarkan sebagai akun Google, Anda juga dapat memakai "Masuk dengan Google" di halaman masuk.')
            ->line('Jika Anda tidak mengharapkan email ini, abaikan saja; tidak ada yang berubah pada akun sebelum kata sandi dibuat.')
            ->salutation('Salam, '.config('app.name'));
    }
}
