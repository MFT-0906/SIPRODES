<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UndanganAkun;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Teks bawaan template email Laravel (subcopy tombol, hak cipta, sapaan) harus berbahasa Indonesia
 * pada email undangan akun dan email atur ulang kata sandi, baik versi HTML maupun teks biasa.
 */
class EmailBahasaIndonesiaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: callable(User): MailMessage}>
     */
    public static function emailAplikasi(): array
    {
        return [
            'undangan akun' => [fn (User $u) => (new UndanganAkun('token-uji'))->toMail($u)],
            'atur ulang kata sandi' => [fn (User $u) => (new ResetPassword('token-uji'))->toMail($u)],
        ];
    }

    /**
     * @param  callable(User): MailMessage  $buat
     */
    #[DataProvider('emailAplikasi')]
    public function test_email_tidak_memuat_teks_bawaan_bahasa_inggris(callable $buat): void
    {
        $this->assertSame('id', app()->getLocale());
        $mail = $buat(User::factory()->create());

        $html = (string) $mail->render();
        $teks = (string) app(Markdown::class)->renderText($mail->markdown, $mail->data());

        foreach (['html' => $html, 'teks' => $teks] as $jenis => $isi) {
            foreach (["If you're having trouble", 'All rights reserved', 'Whoops!', 'Hello!', 'Regards,', 'into your web browser'] as $inggris) {
                $this->assertStringNotContainsString($inggris, $isi, "Email ({$jenis}) masih memuat: {$inggris}");
            }
            $this->assertStringContainsString('Jika tombol "'.$mail->actionText.'" tidak bisa diklik, salin dan tempel alamat berikut ke browser Anda:', html_entity_decode($isi, ENT_QUOTES), "Subcopy tombol ({$jenis})");
            $this->assertStringContainsString('Seluruh hak dilindungi.', $isi, "Hak cipta ({$jenis})");
        }
    }

    public function test_sapaan_bawaan_diterjemahkan_bila_tidak_diisi(): void
    {
        $mail = (new MailMessage)->line('Isi.')->action('Buka', 'https://contoh.test');

        $html = (string) $mail->render();

        $this->assertStringContainsString('Halo!', $html);
        $this->assertStringContainsString('Salam,', $html);
        $this->assertStringNotContainsString('Hello!', $html);
        $this->assertStringNotContainsString('Regards,', $html);
    }
}
