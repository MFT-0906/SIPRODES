<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SimpanProfilRequest;
use App\Models\ProfilDesa;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Kelola Profil Desa (4 tab) ke tabel `profil_desa`. Rute dilindungi middleware `modul:profil`.
 * Lokasi dan kontak resmi hanya tampil di publik setelah dicentang "sudah diverifikasi" dan disimpan sebagai Terbit.
 */
class AdminProfilController extends Controller
{
    public function show(Request $request): View
    {
        $tab = in_array($request->query('tab'), SimpanProfilRequest::TAB, true) ? $request->query('tab') : 'sejarah';
        $profil = ProfilDesa::saatIni();

        return view('pages.admin.profil', [
            'tab' => $tab,
            'data' => $this->data($profil),
            'diperbarui' => $profil->updated_at ? Format::tanggal($profil->updated_at, true) : null,
            'petaUrl' => $profil->lintang !== null && $profil->bujur !== null
                ? 'https://www.google.com/maps?q='.rawurlencode($profil->lintang.','.$profil->bujur)
                : null,
        ]);
    }

    public function simpan(SimpanProfilRequest $request, string $tab): RedirectResponse
    {
        abort_unless(in_array($tab, SimpanProfilRequest::TAB, true), 404);

        $profil = ProfilDesa::saatIni();
        $v = $request->validated();

        match ($tab) {
            'sejarah' => $profil->sejarah = $v['sejarah'],
            'visi' => $this->simpanVisi($profil, $v),
            'lokasi' => $this->simpanLokasi($profil, $v),
            default => $this->simpanData($profil, $v),
        };
        $profil->save();

        return redirect()->route('admin.profil', ['tab' => $tab])->with('toast', 'Perubahan berhasil disimpan.');
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function simpanVisi(ProfilDesa $profil, array $v): void
    {
        $profil->visi = $v['visi'];
        $profil->misi = collect($v['misi'] ?? [])->map(fn ($m) => trim((string) $m))->filter()->values()->all();
        // Teks yang diinput admin dianggap naskah resmi desa (menghapus penanda "menunggu naskah resmi").
        $profil->visi_misi_resmi = true;
    }

    /**
     * Kontak/alamat/peta hanya terbit bila disimpan lewat "Simpan & Terbitkan" dengan kotak verifikasi dicentang.
     *
     * @param  array<string, mixed>  $v
     */
    private function simpanLokasi(ProfilDesa $profil, array $v): void
    {
        $profil->alamat = $v['alamat'];
        $profil->lintang = $v['lintang'];
        $profil->bujur = $v['bujur'];
        $profil->telepon = $v['telepon'] ?? null;
        $profil->email = $v['email'] ?? null;
        $profil->kontak_terverifikasi = ($v['aksi'] ?? 'draf') === 'terbit' && (bool) ($v['terverifikasi'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $v
     */
    private function simpanData(ProfilDesa $profil, array $v): void
    {
        $profil->penduduk = $v['penduduk'];
        $profil->kk = $v['kk'];
        $profil->luas_ha = $v['luas'];
        $profil->jumlah_dusun = $v['dusun'];
        $profil->sumber_data = $v['sumber'];
        $profil->data_terverifikasi = (bool) ($v['terverifikasi'] ?? false);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function data(ProfilDesa $p): array
    {
        return [
            'sejarah' => ['sejarah' => $p->sejarah],
            'visi' => ['visi' => $p->visi, 'misi' => $p->misi ?? []],
            'lokasi' => [
                'alamat' => $p->alamat, 'lintang' => $p->lintang, 'bujur' => $p->bujur,
                'telepon' => $p->telepon, 'email' => $p->email, 'terverifikasi' => (bool) $p->kontak_terverifikasi,
            ],
            'data' => [
                'penduduk' => $p->penduduk, 'kk' => $p->kk,
                'luas' => $p->luas_ha !== null ? (int) $p->luas_ha : null, 'dusun' => $p->jumlah_dusun,
                'sumber' => $p->sumber_data, 'terverifikasi' => (bool) $p->data_terverifikasi,
            ],
        ];
    }
}
