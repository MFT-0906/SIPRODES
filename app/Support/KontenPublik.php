<?php

namespace App\Support;

use App\Enums\JenisAnggaran;
use App\Models\Agenda;
use App\Models\ApbdesPeriode;
use App\Models\Berita;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\PerangkatDesa;
use App\Models\ProfilDesa;
use App\Models\Umkm;
use Illuminate\Database\QueryException;

/**
 * Menyusun data situs publik dari database dalam bentuk yang dipakai template Blade.
 * Hanya konten berstatus `terbit` yang boleh keluar dari kelas ini. Kontak, alamat, peta,
 * WhatsApp, dan kontak kelompok tani hanya dikeluarkan bila sudah diverifikasi/disetujui.
 * Terdaftar sebagai scoped singleton agar profil desa dibaca satu kali per permintaan.
 */
class KontenPublik
{
    private ?ProfilDesa $profil = null;

    public function profilDesa(): ProfilDesa
    {
        if ($this->profil === null) {
            try {
                $this->profil = ProfilDesa::query()->first() ?? new ProfilDesa;
            } catch (QueryException $e) {
                // Header/footer tetap tampil (tanpa kontak) walau database bermasalah, termasuk di halaman galat.
                report($e);
                $this->profil = new ProfilDesa;
            }
        }

        return $this->profil;
    }

    /**
     * Identitas dan kontak desa untuk header, footer, dan halaman kontak.
     *
     * @return array<string, mixed>
     */
    public function identitas(): array
    {
        $profil = $this->profilDesa();
        $wilayah = config('desa.wilayah');
        $terverifikasi = (bool) $profil->kontak_terverifikasi;

        $kontak = fn (?string $nilai) => [
            'verified' => $terverifikasi && filled($nilai),
            'value' => $terverifikasi && filled($nilai) ? $nilai : null,
        ];

        $adaKoordinat = $profil->lintang !== null && $profil->bujur !== null;

        return [
            'sebutan' => $wilayah['sebutan'],
            'nama' => $wilayah['nama'],
            'wilayah' => $wilayah['lengkap'],
            'wilayah_strip' => $wilayah['strip'],
            'wilayah_strip_pendek' => $wilayah['strip_pendek'],
            'kontak' => [
                'alamat' => $kontak($profil->alamat),
                'telepon' => $kontak($profil->telepon),
                'email' => $kontak($profil->email),
                'jam_layanan' => $kontak($profil->jam_layanan),
            ],
            'peta' => [
                'verified' => $terverifikasi && $adaKoordinat,
                'url' => $terverifikasi && $adaKoordinat
                    ? 'https://www.google.com/maps?q='.rawurlencode($profil->lintang.','.$profil->bujur)
                    : null,
                // Koordinat untuk peta interaktif (Leaflet) dan tautan peta besar; hanya ada setelah lokasi diverifikasi.
                'lat' => $terverifikasi && $adaKoordinat ? (float) $profil->lintang : null,
                'lng' => $terverifikasi && $adaKoordinat ? (float) $profil->bujur : null,
                'osm_url' => $terverifikasi && $adaKoordinat
                    ? 'https://www.openstreetmap.org/?mlat='.$profil->lintang.'&mlon='.$profil->bujur.'#map=17/'.$profil->lintang.'/'.$profil->bujur
                    : null,
            ],
        ];
    }

    /**
     * Angka ringkas desa; kosong ditampilkan "—" dan diberi sumber/penanda verifikasi.
     *
     * @return list<array{label: string, value: string, source: string}>
     */
    public function statistik(): array
    {
        $profil = $this->profilDesa();
        $sumber = $profil->sumber_data ?: 'Sumber belum dicantumkan · perlu verifikasi desa';
        $angka = fn (int|float|null $n) => $n === null ? null : number_format((float) $n, 0, ',', '.');
        $tanda = $profil->data_terverifikasi ? '' : '± ';

        return [
            ['label' => 'Jumlah penduduk', 'value' => $profil->penduduk === null ? '—' : $tanda.$angka($profil->penduduk).' jiwa', 'source' => $sumber],
            ['label' => 'Kepala keluarga', 'value' => $profil->kk === null ? '—' : $angka($profil->kk).' KK', 'source' => $sumber],
            ['label' => 'Luas wilayah', 'value' => $profil->luas_ha === null ? '—' : $angka($profil->luas_ha).' ha', 'source' => $sumber],
            ['label' => 'Jumlah dusun', 'value' => $profil->jumlah_dusun === null ? '—' : $profil->jumlah_dusun.' dusun', 'source' => $sumber],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function beranda(): array
    {
        $unggulan = Komoditas::query()->terbit()->orderByDesc('unggulan')->orderBy('nama')->first();

        return [
            'hero' => [
                'eyebrow' => 'Website resmi Pemerintah Desa',
                'judul' => 'Selamat datang di Desa Marga Agung',
                'deskripsi' => 'Desa di Kecamatan Jati Agung, Lampung Selatan, yang dibuka warga transmigrasi bedol desa asal Kaligesik, Magelang. Di sini Anda dapat melihat profil desa, anggaran, potensi, dan kabar terbaru.',
                'foto' => null,
            ],
            'akses' => [
                ['title' => 'APBDes', 'description' => 'Ringkasan & dokumen PDF', 'icon' => 'wallet', 'route' => 'apbdes'],
                ['title' => 'Profil Desa', 'description' => 'Sejarah, visi-misi, perangkat', 'icon' => 'building', 'route' => 'profil'],
                ['title' => 'Potensi Desa', 'description' => 'Pertanian & UMKM', 'icon' => 'sprout', 'route' => 'potensi'],
                ['title' => 'Berita & Agenda', 'description' => 'Kabar & kegiatan terbaru', 'icon' => 'newspaper', 'route' => 'berita.index'],
                ['title' => 'Ruang Aspirasi', 'description' => 'Kirim pertanyaan & saran', 'icon' => 'message', 'route' => 'aspirasi.create'],
            ],
            'potensi' => [
                'pengantar' => 'Sebagian besar warga bekerja sebagai petani, dengan jagung sebagai komoditas utama desa.',
                'unggulan' => $unggulan ? [
                    'badge' => $unggulan->unggulan ? 'Komoditas unggulan' : 'Komoditas',
                    'nama' => $unggulan->nama,
                    'deskripsi' => $unggulan->deskripsi ?? '',
                    'foto_keterangan' => $unggulan->foto_alt ?: 'Foto '.mb_strtolower($unggulan->nama),
                    'foto' => UnggahBerkas::urlGambar($unggulan->foto_path),
                ] : null,
                'umkm' => Umkm::query()->terbit()->orderBy('nama')->limit(2)->get()->map(fn (Umkm $u) => $this->umkm($u))->all(),
            ],
            'berita' => Berita::query()->terbit()->orderByDesc('tanggal')->orderByDesc('id')->limit(2)->get()
                ->map(fn (Berita $b) => [
                    'title' => $b->judul,
                    'excerpt' => $b->ringkasan ?? '',
                    'date' => Format::tanggal($b->tanggal),
                    'category' => $b->kategori,
                    'slug' => $b->slug,
                ])->all(),
            'agenda' => Agenda::query()->terbit()->mendatang()->orderBy('tanggal')->orderBy('waktu')->limit(3)->get()
                ->map(function (Agenda $a) {
                    [$hari, $bulan] = Format::hariBulan($a->tanggal);

                    return ['day' => $hari, 'month' => $bulan, 'title' => $a->judul, 'time' => $this->waktu($a), 'location' => $a->lokasi ?: null];
                })->all(),
            'aspirasi' => [
                'judul' => 'Ada pertanyaan, keluhan, atau saran?',
                'deskripsi' => 'Sampaikan melalui Ruang Aspirasi. Pesan Anda dibaca perangkat desa yang berwenang dan tidak ditampilkan ke publik.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function profil(): array
    {
        $profil = $this->profilDesa();
        $paragraf = collect(preg_split('/\R{2,}/', trim((string) $profil->sejarah)))->filter()->values();

        $visiAda = filled($profil->visi);
        $misi = collect($profil->misi ?? [])->filter(fn ($m) => filled($m))->values()->all();

        $perangkat = PerangkatDesa::query()->tampil()->with('jabatan')->orderBy('urutan')->orderBy('id')->get();

        return [
            'pengantar' => 'Mengenal sejarah, visi dan misi, struktur pemerintahan, serta letak geografis Desa Marga Agung.',
            'sejarah' => [
                'utama' => $paragraf->first() ?? 'Naskah sejarah desa belum diisi.',
                'lanjutan' => $paragraf->slice(1)->implode("\n\n"),
            ],
            'fakta' => collect($this->statistik())
                ->map(fn (array $s) => ['label' => $s['label'], 'value' => $s['value']])
                ->push(['label' => 'Mata pencaharian utama', 'value' => $profil->mata_pencaharian ?: '—'])
                ->all(),
            'fakta_sumber' => $profil->sumber_data ?: 'Sumber belum dicantumkan · perlu verifikasi desa',
            'visi_misi' => [
                'resmi' => $visiAda && $misi !== [] && $profil->visi_misi_resmi,
                'visi' => $visiAda ? $profil->visi : null,
                'misi' => $misi,
            ],
            'perangkat' => [
                'resmi' => $perangkat->isNotEmpty() && $perangkat->every(fn (PerangkatDesa $p) => ! $p->contoh),
                'daftar' => $perangkat->map(fn (PerangkatDesa $p) => [
                    'name' => $p->nama,
                    'position' => $p->jabatan->nama,
                    'photo' => UnggahBerkas::urlGambar($p->foto_path),
                    'photo_alt' => 'Foto '.$p->nama.', '.$p->jabatan->nama,
                ])->all(),
            ],
            'lokasi' => [
                'alamat_wilayah' => $profil->alamat_wilayah ?: config('desa.wilayah.lengkap'),
            ],
        ];
    }

    /**
     * Katalog pertanian, difilter pencarian `$kata` (nama/deskripsi). UMKM dipaginasi di controller.
     *
     * @return array<string, mixed>
     */
    public function potensi(?string $kata = null): array
    {
        $komoditas = Komoditas::query()->terbit()->cari(['nama', 'deskripsi'], $kata)
            ->orderByDesc('unggulan')->orderBy('nama')->get();

        $kelompok = KelompokTani::query()->with(['komoditas', 'dusun'])
            ->when(trim((string) $kata) !== '', function ($q) use ($kata) {
                $q->where(function ($q) use ($kata) {
                    $q->cari(['nama', 'ketua'], $kata)
                        ->orWhereHas('komoditas', fn ($k) => $k->cari(['nama'], $kata));
                });
            })
            ->orderBy('nama')->get();

        $semuaUmkm = Umkm::query()->terbit();

        return [
            'pengantar' => 'Katalog hasil pertanian, kelompok tani, dan UMKM Desa Marga Agung. Kontak hanya ditampilkan bila telah disetujui pemiliknya.',
            'komoditas' => $komoditas->map(fn (Komoditas $k) => [
                'nama' => $k->nama,
                'unggulan' => $k->unggulan,
                'deskripsi' => $k->deskripsi ?? '',
                'musim_panen' => $k->musim_panen,
                'foto_keterangan' => $k->foto_alt ?: 'Foto '.mb_strtolower($k->nama),
                'foto' => UnggahBerkas::urlGambar($k->foto_path),
                'peluang' => [],
            ])->all(),
            'kelompok_tani' => [
                'resmi' => $kelompok->isNotEmpty() && $kelompok->every(fn ($k) => ! $k->contoh),
                'daftar' => $kelompok->map(fn ($k) => [
                    'nama' => $k->nama,
                    'komoditas' => $k->komoditas?->nama ?? '—',
                    'dusun' => $k->dusun?->nama,
                    'kontak' => $k->kontakTampil(),
                    'kontak_disetujui' => (bool) $k->kontak_disetujui && filled($k->kontak),
                ])->all(),
            ],
            'umkm' => [
                'resmi' => (clone $semuaUmkm)->exists() && ! (clone $semuaUmkm)->where('contoh', true)->exists(),
                'per_halaman' => 4,
            ],
            'ajakan' => [
                'judul' => 'Ingin usaha Anda tercantum di sini?',
                'deskripsi' => 'Pelaku usaha di Desa Marga Agung dapat mendaftarkan usahanya melalui kantor desa atau Ruang Aspirasi.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function umkm(Umkm $u): array
    {
        $wa = $u->whatsappTampil();

        return [
            'name' => $u->nama,
            'products' => $u->produk,
            'description' => $u->deskripsi ?? '',
            'whatsapp' => $wa,
            'whatsappApproved' => $wa !== null,
            // Foto produk dan WhatsApp memakai satu persetujuan pemilik usaha.
            'image' => $u->whatsapp_disetujui ? UnggahBerkas::urlGambar($u->foto_path) : null,
            'imageAlt' => $u->foto_alt ?: 'Foto produk '.$u->nama,
        ];
    }

    /**
     * Daftar tahun APBDes yang sudah terbit (terbaru dulu).
     *
     * @return list<int>
     */
    public function tahunApbdes(): array
    {
        return ApbdesPeriode::query()->terbit()->orderByDesc('tahun')->pluck('tahun')->map(fn ($t) => (int) $t)->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function apbdes(int $tahun): array
    {
        $periode = ApbdesPeriode::query()->terbit()->where('tahun', $tahun)->with(['rincian', 'dokumen'])->firstOrFail();

        $tabel = function (JenisAnggaran $jenis) use ($periode) {
            return [
                'rows' => $periode->rincian->where('jenis', $jenis)->map(fn ($r) => ['uraian' => $r->uraian, 'anggaran' => $r->anggaran])->values()->all(),
                'total' => $periode->total($jenis),
            ];
        };

        return [
            'periode' => $periode->periode,
            // false = angka/dokumen masih contoh dan diberi penanda di halaman.
            'resmi' => ! $periode->contoh,
            'ringkasan' => [
                'pendapatan' => $periode->total(JenisAnggaran::Pendapatan),
                'belanja' => $periode->total(JenisAnggaran::Belanja),
                'pembiayaan' => $periode->total(JenisAnggaran::Pembiayaan),
            ],
            'pendapatan' => $tabel(JenisAnggaran::Pendapatan),
            'belanja' => $tabel(JenisAnggaran::Belanja),
            'dokumen' => $periode->dokumen->map(fn ($d) => [
                'judul' => $d->judul,
                'periode' => $d->keterangan_periode,
                'url' => $d->path ? route('apbdes.unduh', $d) : null,
                'ukuran' => $d->path ? $d->ukuran : null,
                'diunggah' => $d->path ? $d->created_at->toDateString() : null,
            ])->all(),
        ];
    }

    /**
     * Bentuk berita untuk template. `isi_html` dirender di server dari teks penanda ringan
     * (HTML mentah dibuang, tautan berbahaya tidak dirender).
     *
     * @return array<string, mixed>
     */
    public static function berita(Berita $b): array
    {
        return [
            'gambar' => UnggahBerkas::urlGambar($b->gambar_path),
            'gambar_alt' => $b->gambar_alt,
            'slug' => $b->slug,
            'title' => $b->judul,
            'excerpt' => $b->ringkasan ?? '',
            'category' => $b->kategori,
            'tanggal' => $b->tanggal->toDateString(),
            'penulis' => 'Admin desa',
            'isi_html' => TeksRingan::html($b->isi),
            'isi_polos' => TeksRingan::polos($b->isi),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function agenda(Agenda $a): array
    {
        return [
            'tanggal' => $a->tanggal->toDateString(),
            'title' => $a->judul,
            'time' => (new self)->waktu($a),
            'location' => $a->lokasi ?: null,
        ];
    }

    private function waktu(Agenda $a): string
    {
        return $a->waktu ? str_replace(':', '.', substr((string) $a->waktu, 0, 5)).' WIB' : 'Waktu menyusul';
    }
}
