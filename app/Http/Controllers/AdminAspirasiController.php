<?php

namespace App\Http\Controllers;

use App\Enums\StatusAspirasi;
use App\Http\Requests\Admin\UbahStatusAspirasiRequest;
use App\Models\Aspirasi;
use App\Models\AspirasiRiwayat;
use App\Support\Aktivitas;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Penanganan Aspirasi Warga. Rute dilindungi middleware `modul:aspirasi`.
 * Aspirasi tidak pernah tampil di halaman publik; NIK hanya tampil disamarkan dan tidak pernah dikirim utuh ke tampilan.
 */
class AdminAspirasiController extends Controller
{
    private const PER_HALAMAN = 20;

    public function index(Request $request): View
    {
        $filter = in_array($request->query('status'), array_column(StatusAspirasi::cases(), 'value'), true) ? $request->query('status') : 'semua';
        $kata = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $daftar = Aspirasi::query()
            ->when($filter !== 'semua', fn ($q) => $q->where('status', $filter))
            ->cari(['nama', 'pesan'], $kata)
            ->latest()->latest('id')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->through(fn (Aspirasi $a) => $this->larik($a));

        $dipilih = $request->query('id')
            ? Aspirasi::query()->with('riwayat.user')->find((int) $request->query('id'))
            : null;
        $bawaan = $dipilih ? null : $daftar->first();
        if ($bawaan) {
            $bawaan = $this->larik(Aspirasi::query()->with('riwayat.user')->find($bawaan['id']), true);
        }

        return view('pages.admin.aspirasi', [
            'daftar' => $daftar,
            'filter' => $filter,
            'jumlahBaru' => Aspirasi::query()->where('status', StatusAspirasi::Baru)->count(),
            // Di layar lebar, detail pertama otomatis terbuka; di HP daftar tampil lebih dulu.
            'dipilih' => $dipilih ? $this->larik($dipilih, true) : null,
            'bawaan' => $bawaan,
            'statusLabel' => ['baru' => 'Baru', 'dibaca' => 'Sudah Dibaca', 'selesai' => 'Selesai'],
            'semuaKosong' => ! Aspirasi::query()->exists(),
        ]);
    }

    public function status(UbahStatusAspirasiRequest $request, int $id): RedirectResponse
    {
        $aspirasi = Aspirasi::query()->findOrFail($id);

        // Tombol yang ditekan menentukan status akhir; tanpa tombol, pilihan "Ubah status" dipakai.
        $baru = StatusAspirasi::from($request->input('aksi') ?: $request->input('status'));
        $catatan = $request->filled('catatan') ? trim($request->string('catatan')->toString()) : null;
        $statusLama = $aspirasi->status;

        DB::transaction(function () use ($aspirasi, $baru, $catatan, $request) {
            $lama = $aspirasi->status;

            if ($baru !== $lama) {
                $aspirasi->status = $baru;
                if ($baru !== StatusAspirasi::Baru) {
                    $aspirasi->dibaca_at ??= now();
                }
                $aspirasi->selesai_at = $baru === StatusAspirasi::Selesai ? now() : null;
                $aspirasi->save();

                $aspirasi->riwayat()->create([
                    'user_id' => $request->user()->id,
                    'aksi' => 'status:'.$baru->value,
                    'catatan' => null,
                ]);
            }

            $catatanTerakhir = $aspirasi->riwayat()->where('aksi', 'catatan')->latest('id')->value('catatan');
            if ($catatan !== null && $catatan !== $catatanTerakhir) {
                $aspirasi->riwayat()->create(['user_id' => $request->user()->id, 'aksi' => 'catatan', 'catatan' => $catatan]);
            }
        });

        if ($baru !== $statusLama) {
            Aktivitas::catat('aspirasi.status', $aspirasi, $statusLama->label().' → '.$baru->label());
        }

        return redirect()->route('admin.aspirasi.index', ['id' => $aspirasi->id])->with('toast', 'Perubahan berhasil disimpan.');
    }

    /**
     * Bentuk yang dipakai tampilan. NIK diberikan hanya dalam bentuk samaran.
     *
     * @return array<string, mixed>
     */
    private function larik(Aspirasi $a, bool $denganRiwayat = false): array
    {
        $data = [
            'id' => $a->id,
            'nama' => $a->nama ?? 'Tanpa nama',
            'pesan' => $a->pesan,
            'diterima' => $a->created_at->format('Y-m-d H:i:s'),
            'status' => $a->status->value,
            'nik_samar' => $denganRiwayat ? Format::nikSamar($a->nik) : null,
            'anonim' => $a->dianonimkan_at !== null,
        ];

        if ($denganRiwayat) {
            $riwayat = $a->riwayat->sortBy('id')->values();
            $data['catatan'] = $riwayat->where('aksi', 'catatan')->last()?->catatan;
            $data['riwayat'] = $riwayat->map(fn (AspirasiRiwayat $r) => [
                'waktu' => $r->created_at->format('Y-m-d H:i:s'),
                'teks' => $this->teksRiwayat($r),
            ])->all();
        }

        return $data;
    }

    private function teksRiwayat(AspirasiRiwayat $r): string
    {
        $oleh = $r->user ? ' oleh '.$r->user->name : '';

        return match (true) {
            $r->aksi === 'diterima' => 'Aspirasi diterima (status: Baru)',
            str_starts_with($r->aksi, 'status:') => 'Status diubah menjadi '.StatusAspirasi::from(substr($r->aksi, 7))->label().$oleh,
            $r->aksi === 'catatan' => 'Catatan tindak lanjut'.$oleh.': '.$r->catatan,
            default => $r->aksi,
        };
    }
}
