<?php

namespace Tests\Feature;

use App\Enums\Modul;
use App\Enums\StatusAspirasi;
use App\Models\Aspirasi;
use App\Models\AspirasiRiwayat;
use App\Models\User;
use App\Support\AntiSpamAspirasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\MasukSebagaiAdmin;
use Tests\TestCase;

class Fase9AspirasiTest extends TestCase
{
    use MasukSebagaiAdmin, RefreshDatabase;

    private const NIK = '1871010101010001';

    /**
     * Isian formulir yang sah. Seperti saat formulir dibuka, jawaban pertanyaan hitungan (7) disiapkan di sesi.
     *
     * @return array<string, mixed>
     */
    private function kirim(array $ganti = []): array
    {
        $this->withSession([AntiSpamAspirasi::SESI_JAWABAN => 7]);

        return $ganti + [
            'nama' => 'Warga Uji', 'pesan' => 'Mohon perbaikan jalan dusun yang rusak setelah hujan.',
            AntiSpamAspirasi::KOLOM_TOKEN => AntiSpamAspirasi::token(30), AntiSpamAspirasi::KOLOM_JEBAKAN => '',
            AntiSpamAspirasi::KOLOM_JAWABAN => '7',
        ];
    }

    // ---- Penyimpanan ----

    public function test_aspirasi_tersimpan_dengan_status_baru_dan_riwayat_diterima(): void
    {
        $this->post(route('aspirasi.store'), $this->kirim())
            ->assertRedirect(route('aspirasi.create'))->assertSessionHas('aspirasi_terkirim');

        $a = Aspirasi::sole();
        $this->assertSame('Warga Uji', $a->nama);
        $this->assertSame(StatusAspirasi::Baru, $a->status);
        $this->assertNull($a->nik);
        $this->assertFalse($a->contoh);
        $this->assertSame(['diterima'], $a->riwayat->pluck('aksi')->all());
    }

    public function test_formulir_tidak_meminta_nik_dan_nik_yang_dikirim_diabaikan(): void
    {
        $this->get(route('aspirasi.create'))->assertOk()
            ->assertDontSee('name="nik"', false)->assertDontSee('NIK');

        $this->post(route('aspirasi.store'), $this->kirim(['nik' => self::NIK]))->assertRedirect();

        $this->assertNull(Aspirasi::sole()->nik);
        $this->assertStringNotContainsString(self::NIK, json_encode(DB::table('aspirasi')->first()));
    }

    public function test_nik_data_lama_tetap_terenkripsi_dan_tampil_disamarkan_di_admin(): void
    {
        $a = Aspirasi::factory()->create(['nik' => self::NIK]);

        $mentah = DB::table('aspirasi')->value('nik');
        $this->assertNotSame(self::NIK, $mentah);
        $this->assertStringNotContainsString(self::NIK, $mentah);

        $this->masukSebagaiAdmin();
        $html = $this->get(route('admin.aspirasi.index', ['id' => $a->id]))->assertOk()->assertSee('•••• •••• •••• 0001')->getContent();
        $this->assertStringNotContainsString(self::NIK, $html);
        $this->assertStringNotContainsString('1871 0101 0101 0001', $html);
    }

    public function test_tidak_ada_ip_atau_data_perangkat_yang_disimpan(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.77'])->withHeaders(['User-Agent' => 'AgenUjiRahasia'])
            ->post(route('aspirasi.store'), $this->kirim())->assertRedirect();

        $baris = json_encode(DB::table('aspirasi')->first()).json_encode(DB::table('aspirasi_riwayat')->get());
        $this->assertStringNotContainsString('203.0.113.77', $baris);
        $this->assertStringNotContainsString('AgenUjiRahasia', $baris);
        $this->assertFalse(Schema::hasColumn('aspirasi', 'ip'));
    }

    public function test_validasi_server_tetap_berlaku(): void
    {
        $this->post(route('aspirasi.store'), $this->kirim(['nama' => '', 'pesan' => 'Jalan rusak']))
            ->assertSessionHasErrors(['nama', 'pesan']);
        $this->post(route('aspirasi.store'), $this->kirim(['pesan' => str_repeat('a', 1001)]))->assertSessionHasErrors('pesan');
        $this->post(route('aspirasi.store'), $this->kirim(['nama' => str_repeat('n', 101)]))->assertSessionHasErrors('nama');

        $this->assertSame(0, Aspirasi::count());
    }

    // ---- Anti-spam ----

    public function test_honeypot_terisi_tidak_menyimpan_apa_pun_tetapi_tampak_berhasil(): void
    {
        $this->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_JEBAKAN => 'http://spam.test']))
            ->assertRedirect(route('aspirasi.create'))->assertSessionHas('aspirasi_terkirim');

        $this->assertSame(0, Aspirasi::count());
    }

    public function test_pengiriman_terlalu_cepat_ditolak_dengan_pesan_umum(): void
    {
        $this->from(route('aspirasi.create'))->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_TOKEN => AntiSpamAspirasi::token(0)]))
            ->assertSessionHasErrors(['formulir' => 'Pesan terkirim terlalu cepat. Periksa isian Anda, tunggu beberapa detik, lalu kirim kembali.']);

        $this->assertSame(0, Aspirasi::count());
    }

    public function test_formulir_menampilkan_pertanyaan_hitungan_dan_jawabannya_hanya_di_sesi(): void
    {
        $html = $this->get(route('aspirasi.create'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/Pertanyaan keamanan: Berapa [1-9] \+ [1-9]\?/', $html);
        $this->assertStringContainsString('name="'.AntiSpamAspirasi::KOLOM_JAWABAN.'"', $html);
        preg_match('/Berapa ([1-9]) \+ ([1-9])\?/', $html, $angka);
        $this->assertSame((int) $angka[1] + (int) $angka[2], session(AntiSpamAspirasi::SESI_JAWABAN));
    }

    public function test_jawaban_hitungan_salah_kosong_atau_tanpa_pertanyaan_ditolak(): void
    {
        foreach (['8', '', 'tujuh', '7.0'] as $jawaban) {
            $this->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_JAWABAN => $jawaban]))
                ->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);
        }

        // Kiriman langsung tanpa membuka formulir (tidak ada pertanyaan di sesi) ditolak walau menebak jawaban.
        $isian = $this->kirim();
        $this->flushSession();
        $this->post(route('aspirasi.store'), $isian)->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);

        $this->assertSame(0, Aspirasi::count());
    }

    public function test_jawaban_salah_tidak_diisikan_kembali_tetapi_isian_lain_tetap(): void
    {
        $this->from(route('aspirasi.create'))->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_JAWABAN => '13']))
            ->assertRedirect(route('aspirasi.create'));

        $this->get(route('aspirasi.create'))->assertOk()
            ->assertSee('value="Warga Uji"', false)
            ->assertDontSee('value="13"', false)
            ->assertSee('Jawaban hitungan belum tepat');
    }

    public function test_satu_jawaban_hitungan_hanya_berlaku_sekali(): void
    {
        $isian = $this->kirim();
        $this->post(route('aspirasi.store'), $isian)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull(session(AntiSpamAspirasi::SESI_JAWABAN));

        // Mengirim ulang isian yang sama tanpa membuka formulir lagi ditolak.
        $this->post(route('aspirasi.store'), $isian)->assertSessionHasErrors(AntiSpamAspirasi::KOLOM_JAWABAN);

        $this->assertSame(1, Aspirasi::count());
    }

    public function test_token_hilang_rusak_atau_kedaluwarsa_ditolak(): void
    {
        foreach ([null, 'asal-asalan', AntiSpamAspirasi::token(86400 + 60)] as $token) {
            $this->post(route('aspirasi.store'), $this->kirim([AntiSpamAspirasi::KOLOM_TOKEN => $token]))->assertSessionHasErrors('formulir');
        }
        $this->post(route('aspirasi.store'), ['nama' => 'X', 'pesan' => 'Pesan tanpa token yang cukup panjang.'])->assertSessionHasErrors('formulir');

        $this->assertSame(0, Aspirasi::count());
    }

    public function test_batas_pengiriman_per_ip_menghasilkan_429(): void
    {
        RateLimiter::clear('aspirasi:'.sha1('127.0.0.1'));

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('aspirasi.store'), $this->kirim())->assertRedirect();
        }
        $this->post(route('aspirasi.store'), $this->kirim())->assertStatus(429);

        $this->assertSame(5, Aspirasi::count());
    }

    public function test_formulir_memuat_token_dan_kolom_jebakan_tersembunyi(): void
    {
        $html = $this->get(route('aspirasi.create'))->assertOk()->getContent();

        $this->assertStringContainsString('name="'.AntiSpamAspirasi::KOLOM_TOKEN.'"', $html);
        $this->assertMatchesRegularExpression('/name="'.AntiSpamAspirasi::KOLOM_JEBAKAN.'"[^>]*tabindex="-1"/', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    // ---- Penanganan admin ----

    private function aspirasi(array $ganti = []): Aspirasi
    {
        return Aspirasi::factory()->create($ganti);
    }

    public function test_admin_melihat_daftar_filter_dan_pencarian_dari_database(): void
    {
        $this->aspirasi(['nama' => 'Pengirim Satu', 'pesan' => 'Soal jalan rusak di dusun dua.']);
        $this->aspirasi(['nama' => 'Pengirim Dua', 'pesan' => 'Soal lampu jalan.']);
        Aspirasi::factory()->selesai()->create(['nama' => 'Pengirim Tiga', 'pesan' => 'Soal surat pengantar.']);
        $this->masukSebagaiAdmin();

        $this->get(route('admin.aspirasi.index'))->assertOk()->assertSee('Baru (2)')->assertSee('Pengirim Satu')->assertSee('Pengirim Tiga');
        $this->get(route('admin.aspirasi.index', ['status' => 'selesai']))->assertSee('Pengirim Tiga')->assertDontSee('Pengirim Satu');
        $this->get(route('admin.aspirasi.index', ['q' => 'lampu']))->assertSee('Pengirim Dua')->assertDontSee('Pengirim Satu');
        $this->get(route('admin.aspirasi.index', ['q' => "%' OR 1=1 --"]))->assertOk()->assertDontSee('Pengirim Satu');
        $this->get(route('admin.aspirasi.index', ['id' => Aspirasi::where('nama', 'Pengirim Satu')->value('id')]))
            ->assertSee('Soal jalan rusak di dusun dua.')->assertDontSee('NIK:')->assertSee('Riwayat penanganan');
    }

    public function test_keadaan_kosong_per_filter(): void
    {
        $this->masukSebagaiAdmin();
        $this->get(route('admin.aspirasi.index'))->assertOk()->assertSee('Belum ada aspirasi');

        Aspirasi::factory()->selesai()->create();
        $this->get(route('admin.aspirasi.index', ['status' => 'baru']))->assertOk()->assertSee('Tidak ada aspirasi baru')->assertSee('Baru (0)');
    }

    public function test_ubah_status_mencatat_riwayat_waktu_dan_pelaku(): void
    {
        $a = $this->aspirasi();
        $admin = $this->masukSebagaiAdmin();
        $admin->update(['name' => 'Bu Sekdes']);

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'aksi' => 'dibaca'])
            ->assertRedirect(route('admin.aspirasi.index', ['id' => $a->id]))->assertSessionHas('toast', 'Perubahan berhasil disimpan.');
        $a->refresh();
        $this->assertSame(StatusAspirasi::Dibaca, $a->status);
        $this->assertNotNull($a->dibaca_at);
        $this->assertNull($a->selesai_at);

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'dibaca', 'aksi' => 'selesai', 'catatan' => 'Sudah diteruskan ke kaur.'])->assertRedirect();
        $a->refresh();
        $this->assertSame(StatusAspirasi::Selesai, $a->status);
        $this->assertNotNull($a->selesai_at);

        $this->get(route('admin.aspirasi.index', ['id' => $a->id]))
            ->assertSee('Status diubah menjadi Sudah Dibaca oleh Bu Sekdes')->assertSee('Status diubah menjadi Selesai oleh Bu Sekdes')
            ->assertSee('Sudah diteruskan ke kaur.')->assertSee('Baru (0)');

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru'])->assertRedirect();
        $this->assertNull($a->fresh()->selesai_at);
        $this->assertNotNull($a->fresh()->dibaca_at);
        $this->assertEqualsCanonicalizing(['status:dibaca', 'catatan', 'status:selesai', 'status:baru'], AspirasiRiwayat::where('aspirasi_id', $a->id)->pluck('aksi')->all());
    }

    public function test_catatan_sama_tidak_digandakan_dan_status_tak_berubah_tidak_menambah_riwayat(): void
    {
        $a = $this->aspirasi();
        $this->masukSebagaiAdmin();

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'catatan' => 'Catatan satu'])->assertRedirect();
        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'catatan' => 'Catatan satu'])->assertRedirect();
        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'catatan' => 'Catatan dua'])->assertRedirect();

        $this->assertSame(2, $a->riwayat()->where('aksi', 'catatan')->count());
        $this->assertSame(0, $a->riwayat()->where('aksi', 'like', 'status:%')->count());
    }

    public function test_ubah_status_memvalidasi_dan_404(): void
    {
        $a = $this->aspirasi();
        $this->masukSebagaiAdmin();

        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'rahasia'])->assertSessionHasErrors('status');
        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'baru', 'catatan' => str_repeat('x', 2001)])->assertSessionHasErrors('catatan');
        $this->put(route('admin.aspirasi.status', 99999), ['status' => 'baru'])->assertNotFound();
        $this->put('/admin/aspirasi/asp-1', ['status' => 'baru'])->assertNotFound();
    }

    public function test_badge_sidebar_dan_ringkasan_mengikuti_database(): void
    {
        $this->aspirasi();
        $this->aspirasi();
        Aspirasi::factory()->dibaca()->create();
        $this->masukSebagaiAdmin();

        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('aspirasiBaru', 2);
    }

    // ---- Privasi & otorisasi ----

    public function test_aspirasi_tidak_pernah_tampil_di_halaman_publik(): void
    {
        $this->aspirasi(['nama' => 'Warga Rahasia Sekali', 'pesan' => 'Isi pesan rahasia yang sangat pribadi.', 'nik' => self::NIK]);

        foreach (['home', 'profil', 'apbdes', 'potensi', 'berita.index', 'aspirasi.create', 'kontak'] as $nama) {
            $this->get(route($nama))->assertOk()->assertDontSee('Warga Rahasia Sekali')->assertDontSee('pesan rahasia yang sangat pribadi')->assertDontSee(self::NIK);
        }
    }

    public function test_hanya_yang_ditugaskan_modul_aspirasi_dapat_mengakses(): void
    {
        $a = $this->aspirasi();

        $this->actingAs(User::factory()->denganModul([Modul::Berita])->create());
        $this->get(route('admin.aspirasi.index'))->assertForbidden();
        $this->put(route('admin.aspirasi.status', $a->id), ['status' => 'selesai'])->assertForbidden();
        $this->assertSame(StatusAspirasi::Baru, $a->fresh()->status);

        auth()->logout();
        $this->get(route('admin.aspirasi.index'))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->denganModul([Modul::Aspirasi])->create());
        $this->get(route('admin.aspirasi.index'))->assertOk();
    }

    // ---- Anonimisasi ----

    public function test_perintah_anonimkan_hanya_untuk_yang_selesai_melewati_masa_simpan(): void
    {
        config(['desa.aspirasi.masa_simpan_bulan' => 24]);

        $lama = $this->aspirasi(['nama' => 'Selesai Lama', 'nik' => self::NIK, 'status' => StatusAspirasi::Selesai, 'dibaca_at' => now()->subMonths(30), 'selesai_at' => now()->subMonths(25)]);
        $baru = $this->aspirasi(['nama' => 'Selesai Baru', 'nik' => self::NIK, 'status' => StatusAspirasi::Selesai, 'selesai_at' => now()->subMonths(23)]);
        $belum = $this->aspirasi(['nama' => 'Belum Selesai', 'nik' => self::NIK, 'created_at' => now()->subMonths(40)]);
        $dibaca = $this->aspirasi(['nama' => 'Hanya Dibaca', 'status' => StatusAspirasi::Dibaca, 'dibaca_at' => now()->subMonths(40)]);

        $this->artisan('aspirasi:anonimkan', ['--hitung' => true])->expectsOutputToContain('1 aspirasi memenuhi syarat')->assertSuccessful();
        $this->assertSame('Selesai Lama', $lama->fresh()->nama);

        $this->artisan('aspirasi:anonimkan')->expectsOutputToContain('1 aspirasi dianonimkan.')->assertSuccessful();

        $lama->refresh();
        $this->assertNull($lama->nama);
        $this->assertNull($lama->nik);
        $this->assertNotNull($lama->dianonimkan_at);
        $this->assertNotEmpty($lama->pesan);
        $this->assertSame('Selesai Baru', $baru->fresh()->nama);
        $this->assertSame(self::NIK, $baru->fresh()->nik);
        $this->assertSame('Belum Selesai', $belum->fresh()->nama);
        $this->assertSame('Hanya Dibaca', $dibaca->fresh()->nama);

        // Idempoten: tidak memproses ulang.
        $this->artisan('aspirasi:anonimkan')->expectsOutputToContain('0 aspirasi dianonimkan.');

        $this->masukSebagaiAdmin();
        $this->get(route('admin.aspirasi.index', ['id' => $lama->id]))->assertSee('Tanpa nama')->assertSee('NIK: dianonimkan');
    }

    public function test_perintah_anonimkan_terdaftar_di_penjadwal_dan_menghormati_masa_simpan_nol(): void
    {
        $this->assertStringContainsString('aspirasi:anonimkan', Artisan::call('schedule:list') === 0 ? Artisan::output() : '');

        config(['desa.aspirasi.masa_simpan_bulan' => 0]);
        $this->artisan('aspirasi:anonimkan')->expectsOutputToContain('Masa simpan tidak diatur')->assertSuccessful();
    }
}
