<?php

namespace Tests\Feature;

use App\Models\Aspirasi;
use App\Support\AntiSpamAspirasi;
use App\Support\Turnstile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Cloudflare Turnstile pada Ruang Aspirasi, dengan pertanyaan hitungan sebagai cadangan.
 * Semua panggilan ke Cloudflare ditiru (Http::fake); tes tidak membutuhkan internet.
 */
class TurnstileAspirasiTest extends TestCase
{
    use RefreshDatabase;

    private const SITE_KEY = '1x00000000000000000000AA';

    protected function setUp(): void
    {
        parent::setUp();
        // Jangan bergantung pada server dev Vite yang mungkin sedang berjalan (public/hot).
        Vite::useHotFile(storage_path('framework/testing/vite-hot-tidak-ada'));
    }

    private function aktifkan(): void
    {
        config(['desa.turnstile.site_key' => self::SITE_KEY, 'desa.turnstile.secret_key' => 'rahasia-uji']);
    }

    /** @return array<string, mixed> */
    private function kirim(array $ganti = [], ?int $jawabanSesi = 7): array
    {
        if ($jawabanSesi !== null) {
            $this->withSession([AntiSpamAspirasi::SESI_JAWABAN => $jawabanSesi]);
        }

        return $ganti + [
            'nama' => 'Warga Uji', 'pesan' => 'Mohon perbaikan jalan dusun yang rusak setelah hujan.',
            AntiSpamAspirasi::KOLOM_TOKEN => AntiSpamAspirasi::token(30), AntiSpamAspirasi::KOLOM_JEBAKAN => '',
        ];
    }

    public function test_tanpa_kunci_turnstile_tidak_aktif_dan_csp_tidak_mengizinkan_cloudflare(): void
    {
        $respons = $this->get(route('aspirasi.create'))->assertOk()
            ->assertDontSee('data-sitekey', false)
            ->assertSee('Pertanyaan keamanan')
            ->assertDontSee('Cloudflare Turnstile');

        $csp = $respons->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString(Turnstile::ORIGIN, $csp);
        $this->assertStringNotContainsString('frame-src', $csp);
    }

    public function test_dengan_kunci_widget_tampil_dan_csp_mengizinkan_cloudflare(): void
    {
        $this->aktifkan();

        $respons = $this->get(route('aspirasi.create'))->assertOk()
            ->assertSee('data-sitekey="'.self::SITE_KEY.'"', false)
            ->assertSee('Formulir dilindungi Cloudflare Turnstile untuk mencegah spam.')
            // Pertanyaan hitungan tetap ada sebagai cadangan bila widget gagal dimuat.
            ->assertSee('Pertanyaan keamanan');

        $csp = $respons->headers->get('Content-Security-Policy');
        foreach (['script-src', 'frame-src', 'connect-src'] as $arahan) {
            $this->assertMatchesRegularExpression('#'.$arahan.' [^;]*'.preg_quote(Turnstile::ORIGIN, '#').'#', $csp);
        }
        $this->assertStringNotContainsString('rahasia-uji', $respons->getContent());
    }

    public function test_token_turnstile_sah_diterima_tanpa_pertanyaan_hitungan(): void
    {
        $this->aktifkan();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->post(route('aspirasi.store'), $this->kirim([Turnstile::KOLOM_TOKEN => 'token-sah'], jawabanSesi: null))
            ->assertRedirect(route('aspirasi.create'))->assertSessionHasNoErrors();

        $this->assertSame(1, Aspirasi::count());
        Http::assertSent(function (Request $r) {
            return str_ends_with($r->url(), '/turnstile/v0/siteverify')
                && $r['secret'] === 'rahasia-uji' && $r['response'] === 'token-sah'
                && ! isset($r['remoteip']); // IP pengunjung tidak dikirim ke Cloudflare.
        });
    }

    public function test_token_turnstile_ditolak_cloudflare_tidak_bisa_diakali_dengan_jawaban_hitungan(): void
    {
        $this->aktifkan();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        $this->post(route('aspirasi.store'), $this->kirim([Turnstile::KOLOM_TOKEN => 'token-palsu', AntiSpamAspirasi::KOLOM_JAWABAN => '7']))
            ->assertSessionHasErrors('formulir');

        $this->assertSame(0, Aspirasi::count());
    }

    public function test_tanpa_token_formulir_memakai_pertanyaan_hitungan(): void
    {
        $this->aktifkan();
        Http::fake();

        $this->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_JAWABAN => '8']))
            ->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);
        $this->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_JAWABAN => '7']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Aspirasi::count());
        Http::assertNothingSent();
    }

    public function test_cloudflare_tidak_dapat_dihubungi_beralih_ke_pertanyaan_hitungan(): void
    {
        $this->aktifkan();
        Http::fake(fn () => throw new ConnectionException('Cloudflare tidak terjangkau'));

        $this->post(route('aspirasi.store'), $this->kirim([Turnstile::KOLOM_TOKEN => 'token-apa-pun']))
            ->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);
        $this->post(route('aspirasi.store'), $this->kirim([Turnstile::KOLOM_TOKEN => 'token-apa-pun', AntiSpamAspirasi::KOLOM_JAWABAN => '7']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Aspirasi::count());
    }

    public function test_turnstile_tidak_aktif_mengabaikan_token_dan_tetap_meminta_jawaban(): void
    {
        Http::fake();

        $this->post(route('aspirasi.store'), $this->kirim([Turnstile::KOLOM_TOKEN => 'token-sah']))
            ->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);

        $this->assertSame(0, Aspirasi::count());
        Http::assertNothingSent();
    }
}
