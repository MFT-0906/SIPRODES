@extends('layouts.admin')

@section('title', 'Ringkasan')
@section('judul', 'Ringkasan')

@php
    use App\Enums\Modul;
    use App\Support\Format;
    use Illuminate\Support\Facades\Gate;

    // Pintasan hanya untuk modul yang boleh dibuka akun ini (izin sebenarnya di middleware `modul:*`).
    $pintasan = collect([
        ['Tambah berita', 'newspaper', route('admin.berita.create'), Modul::Berita],
        ['Tambah agenda', 'calendar', route('admin.agenda.index').'#form-agenda', Modul::Berita],
        ['Unggah dokumen APBDes', 'upload', route('admin.apbdes'), Modul::Apbdes],
        ['Tambah UMKM', 'store', route('admin.umkm'), Modul::Potensi],
        ['Perbarui struktur perangkat', 'users', route('admin.struktur'), Modul::Profil],
    ])->filter(fn (array $p) => Gate::allows('akses-modul', $p[3]));

    // Staf diberi tahu modul apa saja yang ditugaskan kepadanya, agar jelas mengapa menu lain tidak ada.
    $modulSaya = collect(Modul::untukStaf())
        ->filter(fn (Modul $modul) => Gate::allows('akses-modul', $modul))
        ->map(fn (Modul $modul) => $modul->label())
        ->values()
        ->all();
    // Sapaan menurut jam setempat (zona waktu aplikasi).
    $jam = (int) now()->format('G');
    $sapaan = match (true) {
        $jam < 11 => 'Selamat pagi',
        $jam < 15 => 'Selamat siang',
        $jam < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $namaDepan = \Illuminate\Support\Str::of($pengguna['name'])->before(' ')->toString() ?: $pengguna['name'];
    $logo = \App\Support\LogoDaerah::data();

    $selesai = collect($kelengkapan)->where('selesai', true)->count();
    $totalButir = count($kelengkapan);
    $persen = $totalButir > 0 ? (int) round($selesai / $totalButir * 100) : 100;
    // Butir yang belum selesai ditampilkan lebih dulu.
    $butirUrut = collect($kelengkapan)->sortBy(fn (array $b) => $b['selesai'] ? 1 : 0)->values();

    $deskripsi = match (true) {
        $peranLabel !== 'Staf' => 'Ringkasan konten situs, dihitung dari data terbaru.',
        $modulSaya === [] => 'Belum ada modul yang ditugaskan kepada Anda. Hubungi Super Admin desa.',
        default => 'Modul Anda: '.implode(', ', $modulSaya).'.',
    };
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        {{-- Sapaan: pita hijau dengan identitas, senada dengan halaman masuk. --}}
        <section aria-labelledby="sapaan-judul" class="relative overflow-hidden rounded-[20px] bg-primary-dark px-6 py-7 text-surface sm:px-8">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,rgb(249_168_37/0.22),transparent_45%),radial-gradient(circle_at_10%_110%,rgb(255_255_255/0.10),transparent_40%)]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-16 -top-20 size-64 rounded-full border-[28px] border-surface/5" aria-hidden="true"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($logo)
                        <img src="{{ $logo['url'] }}" alt="" width="{{ $logo['lebar'] }}" height="{{ $logo['tinggi'] }}" loading="lazy" decoding="async" class="hidden h-16 w-auto shrink-0 object-contain drop-shadow-[0_4px_12px_rgb(0_0_0/0.3)] sm:block">
                    @endif
                    <div class="flex flex-col gap-1">
                        <p class="type-label-sm uppercase tracking-[0.14em] text-accent-soft">{{ now()->locale('id')->translatedFormat('l, j F Y') }}</p>
                        <h1 id="sapaan-judul" class="type-h2 text-surface">{{ $sapaan }}, {{ $namaDepan }}</h1>
                        <p class="type-body-sm text-surface/80">{{ $deskripsi }}</p>
                    </div>
                </div>
                @can('akses-modul', Modul::Berita)
                    <x-ui.button :href="route('admin.berita.create')" variant="accent" icon="plus" class="self-start sm:self-auto">Tambah Berita</x-ui.button>
                @endcan
            </div>
        </section>

        {{-- Kelengkapan data resmi: hilang sendiri bila semua butir selesai. --}}
        @if ($totalButir > 0 && $selesai < $totalButir)
            <section aria-labelledby="kelengkapan-judul" class="rounded-card border border-border bg-surface p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex flex-col gap-1">
                        <h2 id="kelengkapan-judul" class="type-h3 text-text">Kelengkapan data situs</h2>
                        <p class="type-body-sm text-muted">Lengkapi data resmi agar situs siap dibaca warga. {{ $selesai }} dari {{ $totalButir }} selesai.</p>
                    </div>
                    <p class="type-h2 tabular-nums text-primary">{{ $persen }}%</p>
                </div>
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-accent-soft" role="progressbar" aria-label="Kelengkapan data situs" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $persen }}">
                    <div class="h-full rounded-full bg-accent" style="width: {{ max($persen, 3) }}%"></div>
                </div>
                <ul class="mt-4 grid gap-2 md:grid-cols-2">
                    @foreach ($butirUrut as $butir)
                        <li>
                            <a href="{{ $butir['href'] }}" @class([
                                'group flex min-h-14 items-center gap-3 rounded-[12px] border p-3 transition-colors',
                                'border-border bg-background hover:border-primary/40 hover:bg-primary-soft' => ! $butir['selesai'],
                                'border-transparent bg-success-soft/60' => $butir['selesai'],
                            ])>
                                @if ($butir['selesai'])
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-success text-surface"><x-ui.icon name="check" class="size-4" /></span>
                                @else
                                    <span class="size-7 shrink-0 rounded-full border-2 border-dashed border-accent-dark" aria-hidden="true"></span>
                                @endif
                                <span class="flex min-w-0 flex-1 flex-col">
                                    <span @class(['type-label', 'text-text' => ! $butir['selesai'], 'text-muted line-through decoration-1' => $butir['selesai']])>
                                        {{ $butir['label'] }}<span class="sr-only">{{ $butir['selesai'] ? ' (selesai)' : ' (belum)' }}</span>
                                    </span>
                                    @unless ($butir['selesai'])
                                        <span class="type-label-sm text-muted">{{ $butir['keterangan'] }}</span>
                                    @endunless
                                </span>
                                @unless ($butir['selesai'])
                                    <x-ui.icon name="chevron-right" class="size-4 shrink-0 text-muted transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
                                @endunless
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Semua angka dihitung dari database; kartu hanya tampil untuk modul yang boleh dibuka akun ini. --}}
        <section aria-label="Statistik konten" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 xl:grid-cols-5">
            @can('akses-modul', Modul::Aspirasi)
                <x-admin.kartu-ringkas class="max-sm:col-span-2" label="Aspirasi baru" :nilai="$aspirasiBaru" keterangan="belum diproses" ikon="message" warna="merah" :sorot="true" :href="route('admin.aspirasi.index')" />
            @endcan
            @can('akses-modul', Modul::Berita)
                <x-admin.kartu-ringkas label="Berita terbit" :nilai="$terbit" keterangan="tampil di situs publik" ikon="newspaper" warna="hijau" :href="route('admin.berita.create')" aksi="Tulis berita" />
                <x-admin.kartu-ringkas label="Berita draf" :nilai="$draf" keterangan="belum dipublikasikan" ikon="edit" warna="biru" />
            @endcan
            @can('akses-modul', Modul::Potensi)
                <x-admin.kartu-ringkas label="UMKM terdaftar" :nilai="$umkm" keterangan="terbit di situs publik" ikon="store" warna="kuning" :href="route('admin.umkm')" aksi="Tambah" />
                <x-admin.kartu-ringkas label="Komoditas" :nilai="$komoditas" :keterangan="$kelompokTani.' kelompok tani'" ikon="sprout" warna="hijau" :href="route('admin.pertanian')" aksi="Tambah" />
            @endcan
        </section>

        <div class="grid items-stretch gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            {{-- Pengunjung: satu kartu dengan grafik 14 hari. Hanya untuk modul Statistik Pengunjung. --}}
            @if ($kunjungan)
                @php
                    $angka = fn (int $n) => number_format($n, 0, ',', '.');
                    $puncak = max(1, ...array_map(fn ($h) => $h['pengunjung'], $harian ?: [['pengunjung' => 0]]));
                @endphp
                <section aria-labelledby="kunjungan-judul" class="flex flex-col gap-5 rounded-card border border-border bg-surface p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex size-9 items-center justify-center rounded-[10px] bg-primary-soft text-primary"><x-ui.icon name="chart" class="size-[18px]" /></span>
                            <h2 id="kunjungan-judul" class="type-h3 text-text">Pengunjung situs</h2>
                        </div>
                        <x-ui.button :href="route('admin.statistik')" variant="ghost" size="s" icon="arrow-right">Lihat statistik</x-ui.button>
                    </div>
                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ([['Hari ini', $kunjungan['pengunjung_hari_ini'], true], ['7 hari', $kunjungan['pengunjung_7_hari'], false], ['30 hari', $kunjungan['pengunjung_30_hari'], false], ['Total', $kunjungan['pengunjung_total'], false]] as [$teks, $nilai, $utama])
                            <div class="flex flex-col gap-0.5">
                                <dt class="type-label-sm text-muted">{{ $teks }}</dt>
                                <dd @class(['tabular-nums', 'type-h1 text-primary' => $utama, 'type-h2 text-text' => ! $utama])>{{ $angka($nilai) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @if ($harian)
                        <figure class="flex flex-1 flex-col gap-2">
                            <div class="flex min-h-28 flex-1 items-end gap-1.5" role="img" aria-label="Grafik pengunjung 14 hari terakhir, tertinggi {{ $angka($puncak) }} pengunjung per hari">
                                @foreach ($harian as $i => $h)
                                    @php $hariIni = $i === count($harian) - 1; @endphp
                                    <div class="flex h-full flex-1 flex-col justify-end" title="{{ \Illuminate\Support\Carbon::parse($h['tanggal'])->locale('id')->translatedFormat('j M') }}: {{ $h['pengunjung'] }} pengunjung">
                                        <div @class(['w-full rounded-t-[5px]', 'bg-accent' => $hariIni, 'bg-primary/75' => ! $hariIni]) style="height: {{ $h['pengunjung'] > 0 ? max(6, round($h['pengunjung'] / $puncak * 100)) : 3 }}%"></div>
                                    </div>
                                @endforeach
                            </div>
                            <figcaption class="flex justify-between type-label-sm text-muted">
                                <span>{{ \Illuminate\Support\Carbon::parse($harian[0]['tanggal'])->locale('id')->translatedFormat('j M') }}</span>
                                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-accent" aria-hidden="true"></span>Hari ini · {{ $angka($kunjungan['tayang_hari_ini']) }} tayangan</span>
                            </figcaption>
                        </figure>
                    @endif
                </section>
            @endif

            <section aria-labelledby="pintasan-judul" class="flex flex-col gap-2.5 rounded-card border border-border bg-surface p-5">
                <h2 id="pintasan-judul" class="type-h3 text-text">Pintasan</h2>
                @foreach ($pintasan as [$teks, $ikon, $tujuan])
                    <a href="{{ $tujuan }}" class="group flex min-h-11 items-center gap-3 rounded-[10px] bg-background p-3 type-label text-text transition-colors hover:bg-primary-soft">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-[8px] bg-surface text-primary shadow-[0_1px_2px_rgb(15_51_33/0.08)]"><x-ui.icon :name="$ikon" class="size-4" /></span>
                        <span class="min-w-0 flex-1">{{ $teks }}</span>
                        <x-ui.icon name="chevron-right" class="size-4 shrink-0 text-muted transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
                    </a>
                @endforeach
            </section>
        </div>

        @can('akses-modul', Modul::Aspirasi)
            {{-- Aspirasi terbaru: hanya cuplikan; NIK tidak pernah ditampilkan. --}}
            <section aria-labelledby="aspirasi-terbaru-judul" class="flex flex-col rounded-card border border-border bg-surface">
                <div class="flex items-center justify-between gap-3 px-5 py-4">
                    <h2 id="aspirasi-terbaru-judul" class="type-h3 text-text">Aspirasi Terbaru</h2>
                    <x-ui.button :href="route('admin.aspirasi.index')" variant="ghost" size="s" icon="arrow-right">Lihat semua</x-ui.button>
                </div>
                <ul>
                    @forelse ($aspirasiTerbaru as $a)
                        <li class="flex items-center gap-3 border-t border-border px-5 py-3.5">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft type-label text-primary" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($a['pengirim'], 0, 1)) }}</span>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <p class="type-label text-text">{{ $a['pengirim'] }}</p>
                                <p class="truncate type-body-sm text-muted">{{ $a['cuplikan'] }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <x-admin.status-badge :status="$a['status']" />
                                <span class="type-label-sm text-muted">{{ $a['waktu'] }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="flex items-center gap-3 border-t border-border px-5 py-6">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-success-soft text-success"><x-ui.icon name="check" class="size-4" /></span>
                            <p class="type-body text-muted">Belum ada aspirasi masuk.</p>
                        </li>
                    @endforelse
                </ul>
            </section>
        @endcan

        @can('akses-modul', Modul::Berita)
        <section aria-labelledby="draf-terakhir-judul" class="rounded-card border border-border bg-surface">
            <h2 id="draf-terakhir-judul" class="type-h3 px-5 py-4 text-text">Konten Draf Terakhir</h2>
            @forelse ($konten as $k)
                <div class="flex flex-col gap-3 border-t border-border px-5 py-3.5 sm:flex-row sm:items-center sm:gap-4">
                    <p class="min-w-0 flex-1 type-label text-text">{{ $k['judul'] }}</p>
                    <p class="type-body-sm text-muted">{{ $k['jenis'] }}</p>
                    <p class="type-body-sm text-muted">Diubah {{ Format::tanggal($k['diubah']) }}</p>
                    <x-admin.status-badge status="draf" class="self-start sm:self-auto" />
                    <x-ui.button :href="$k['href']" variant="secondary" size="s" icon="edit" class="self-start sm:self-auto">Edit<span class="sr-only"> {{ $k['jenis'] }}: {{ $k['judul'] }}</span></x-ui.button>
                </div>
            @empty
                <p class="border-t border-border px-5 py-6 type-body text-muted">Tidak ada konten draf. Semua konten sudah diterbitkan.</p>
            @endforelse
        </section>
        @endcan
    </div>
@endsection
