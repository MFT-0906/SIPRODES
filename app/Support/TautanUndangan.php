<?php

namespace App\Support;

/**
 * Tautan undangan akun memakai halaman atur kata sandi yang sama dengan tautan lupa kata sandi, tetapi masa
 * berlakunya mengikuti broker "undangan". Kedua jenis token berada di tabel yang sama, sehingga jenis tautan
 * dibuktikan dengan tanda HMAC (kunci aplikasi) atas token dan email. Tanpa tanda yang sah, token selalu
 * diperiksa dengan broker "users" (60 menit), jadi token lupa kata sandi tidak dapat "diperpanjang" lewat parameter.
 */
final class TautanUndangan
{
    public const BROKER = 'undangan';

    public static function masaBerlakuJam(): int
    {
        return intdiv((int) config('auth.passwords.'.self::BROKER.'.expire'), 60);
    }

    public static function url(string $email, string $token): string
    {
        return route('admin.password.reset', [
            'token' => $token,
            'email' => $email,
            'tanda' => self::tanda($email, $token),
        ]);
    }

    public static function tanda(string $email, string $token): string
    {
        return hash_hmac('sha256', 'undangan|'.mb_strtolower($email).'|'.$token, (string) config('app.key'));
    }

    public static function sah(?string $tanda, string $email, string $token): bool
    {
        return is_string($tanda) && $tanda !== '' && hash_equals(self::tanda($email, $token), $tanda);
    }
}
