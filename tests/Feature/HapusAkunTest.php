<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\LogAktivitas;
use App\Models\User;
use App\Support\Aktivitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Super Admin menghapus akun admin: aman (bukan diri sendiri, bukan Super Admin aktif terakhir) dan riwayat tetap utuh.
 */
class HapusAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_menghapus_akun_staf(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $staf = User::factory()->create(['name' => 'Staf Salah Ketik', 'email' => 'salah@contoh.id']);
        DB::table('password_reset_tokens')->insert(['email' => 'salah@contoh.id', 'token' => 'x', 'created_at' => now()]);

        $this->actingAs($admin)->get(route('admin.akun.index'))->assertOk()
            ->assertSee('Hapus akun: Staf Salah Ketik')
            ->assertDontSee('Hapus akun: '.$admin->name);

        $this->actingAs($admin)->delete(route('admin.akun.destroy', $staf))
            ->assertRedirect(route('admin.akun.index'))->assertSessionHas('toast', 'Akun “Staf Salah Ketik” dihapus.');

        $this->assertModelMissing($staf);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'salah@contoh.id']);
        $log = LogAktivitas::query()->where('aksi', 'akun.dihapus')->sole();
        $this->assertStringContainsString('salah@contoh.id', $log->keterangan);
        $this->assertSame($admin->id, $log->user_id);
    }

    public function test_riwayat_tetap_ada_dan_menyebut_nama_pelaku(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $staf = User::factory()->create(['name' => 'Bu Sekdes', 'email' => 'sekdes@contoh.id']);
        $berita = Berita::factory()->create(['penulis_id' => $staf->id]);

        $this->actingAs($staf);
        Aktivitas::catat('berita.dibuat', $berita, 'Judul uji');

        $this->actingAs($admin)->delete(route('admin.akun.destroy', $staf));

        $this->assertModelExists($berita);
        $this->assertNull($berita->refresh()->penulis_id);
        $log = LogAktivitas::query()->where('aksi', 'berita.dibuat')->sole();
        $this->assertNull($log->user_id);
        $this->assertSame('Bu Sekdes (sekdes@contoh.id)', $log->pelaku_nama);

        $this->actingAs($admin)->get(route('admin.akun.index'))->assertSee('Bu Sekdes (sekdes@contoh.id) · akun dihapus');
    }

    public function test_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->superAdmin()->create();

        $this->actingAs($admin)->delete(route('admin.akun.destroy', $admin))
            ->assertRedirect(route('admin.akun.index'))->assertSessionHas('toast', 'Anda tidak dapat menghapus akun Anda sendiri.');
        $this->assertModelExists($admin);
    }

    public function test_super_admin_nonaktif_boleh_dihapus(): void
    {
        $aktif = User::factory()->superAdmin()->create();
        $nonaktif = User::factory()->superAdmin()->nonaktif()->create();

        $this->actingAs($aktif)->delete(route('admin.akun.destroy', $nonaktif))->assertRedirect(route('admin.akun.index'));
        $this->assertModelMissing($nonaktif);
    }

    public function test_menghapus_super_admin_lain_boleh_bila_masih_ada_yang_aktif(): void
    {
        $a = User::factory()->superAdmin()->create();
        $b = User::factory()->superAdmin()->create();

        $this->actingAs($a)->delete(route('admin.akun.destroy', $b));
        $this->assertModelMissing($b);
        $this->assertTrue($a->refresh()->adalahSuperAdmin());
    }

    public function test_akun_terhapus_langsung_kehilangan_akses(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $staf = User::factory()->create();

        $this->actingAs($admin)->delete(route('admin.akun.destroy', $staf));

        auth()->forgetGuards();
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_staf_tidak_bisa_menghapus_akun(): void
    {
        $staf = User::factory()->create();
        $lain = User::factory()->create();

        $this->actingAs($staf)->delete(route('admin.akun.destroy', $lain))->assertForbidden();
        $this->assertModelExists($lain);
    }
}
