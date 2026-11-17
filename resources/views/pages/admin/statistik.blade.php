@extends('layouts.admin')

@section('title', 'Statistik Pengunjung')
@section('judul', 'Statistik Pengunjung')
@section('breadcrumb', 'Dashboard / Layanan / Statistik Pengunjung')

@php
    use Carbon\Carbon;

    $angka = fn (int $n) => number_format($n, 0, ',', '.');
    $puncak = max(1, collect($harian)->max('pengunjung'));
    $jumlah30 = collect($harian)->sum('pengunjung');
    $kolomHalaman = 'minmax(0,1fr) 140px';
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        <x-admin.page-header title="Statistik Pengunjung" description="Jumlah kunjungan ke halaman publik, dihitung internal tanpa cookie dan tanpa pihak ketiga." />

        @unless ($aktif)
            <x-ui.alert tone="peringatan" title="Penghitungan sedang dimatikan">Penghitungan kunjungan dimatikan lewat pengaturan <code>STATISTIK_AKTIF</code>. Angka di bawah hanya data yang sudah tercatat sebelumnya.</x-ui.alert>
        @endunless

        <section aria-label="Ringkasan pengunjung" class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            <x-ui.card-stat label="Pengunjung hari ini" :value="$angka($ringkasan['pengunjung_hari_ini'])" source="unik per hari" />
            <x-ui.card-stat label="Pengunjung 7 hari" :value="$angka($ringkasan['pengunjung_7_hari'])" source="7 hari terakhir" />
            <x-ui.card-stat label="Pengunjung 30 hari" :value="$angka($ringkasan['pengunjung_30_hari'])" source="30 hari terakhir" />
            <x-ui.card-stat label="Total pengunjung" :value="$angka($ringkasan['pengunjung_total'])" source="sejak pencatatan dimulai" />
            <x-ui.card-stat label="Tayangan hari ini" :value="$angka($ringkasan['tayang_hari_ini'])" source="halaman dibuka" />
            <x-ui.card-stat label="Total tayangan" :value="$angka($ringkasan['tayang_total'])" source="sejak pencatatan dimulai" />
        </section>

        {{-- Grafik batang 30 hari: HTML/CSS biasa. Tabel tersembunyi di bawahnya memuat angka yang sama untuk pembaca layar. --}}
        <section aria-labelledby="grafik-judul" class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-5">
            <div class="flex flex-col gap-1">
                <h2 id="grafik-judul" class="type-h3 text-text">Pengunjung 30 hari terakhir</h2>
                <p class="type-body-sm text-muted">{{ $angka($jumlah30) }} pengunjung per hari dijumlahkan. Orang yang sama di hari berbeda dihitung sekali per hari.</p>
            </div>

            <div aria-hidden="true" class="flex h-48 items-end gap-[3px] border-b border-border">
                @foreach ($harian as $h)
                    @php
                        $tinggi = $h['pengunjung'] === 0 ? 0 : max(3, (int) round($h['pengunjung'] / $puncak * 100));
                    @endphp
                    <div class="group relative flex h-full min-w-0 flex-1 items-end" title="{{ Carbon::parse($h['tanggal'])->locale('id')->translatedFormat('j F Y') }}: {{ $h['pengunjung'] }} pengunjung, {{ $h['tayang'] }} tayangan">
                        <div class="w-full rounded-t-[3px] bg-primary transition-colors group-hover:bg-accent" style="height: {{ $tinggi }}%"></div>
                    </div>
                @endforeach
            </div>
            <div aria-hidden="true" class="flex justify-between type-label-sm text-muted">
                <span>{{ Carbon::parse($harian[0]['tanggal'])->locale('id')->translatedFormat('j M') }}</span>
                <span>Puncak {{ $angka($puncak === 1 && $jumlah30 === 0 ? 0 : $puncak) }} / hari</span>
                <span>{{ Carbon::parse($harian[29]['tanggal'])->locale('id')->translatedFormat('j M') }}</span>
            </div>

            <table class="sr-only">
                <caption>Pengunjung dan tayangan per hari, 30 hari terakhir</caption>
                <thead><tr><th scope="col">Tanggal</th><th scope="col">Pengunjung</th><th scope="col">Tayangan</th></tr></thead>
                <tbody>
                    @foreach ($harian as $h)
                        <tr><th scope="row">{{ Carbon::parse($h['tanggal'])->locale('id')->translatedFormat('j F Y') }}</th><td>{{ $h['pengunjung'] }}</td><td>{{ $h['tayang'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section aria-labelledby="populer-judul" class="rounded-[14px] border border-border bg-surface">
            <div class="flex flex-col gap-1 p-5">
                <h2 id="populer-judul" class="type-h3 text-text">10 halaman terpopuler (30 hari terakhir)</h2>
                <p class="type-body-sm text-muted">Diurutkan menurut jumlah tayangan.</p>
            </div>
            @if ($terpopuler === [])
                <p class="border-t border-border px-5 py-6 type-body text-muted">Belum ada kunjungan tercatat dalam 30 hari terakhir.</p>
            @else
                <x-admin.table :cols="$kolomHalaman" caption="Sepuluh halaman publik dengan tayangan terbanyak dalam 30 hari terakhir" :heads="[['HALAMAN', 'left'], ['TAYANGAN', 'right']]">
                    @foreach ($terpopuler as $baris)
                        <x-admin.tr :cols="$kolomHalaman">
                            <th scope="row" class="break-all text-left type-label text-text">{{ $baris['path'] }}</th>
                            <td class="type-body tabular-nums text-text lg:text-right"><span class="type-label-sm text-muted lg:sr-only">Tayangan: </span>{{ $angka($baris['tayang']) }}</td>
                        </x-admin.tr>
                    @endforeach
                </x-admin.table>
            @endif
        </section>

        <p class="type-body-sm text-muted">Yang disimpan hanya angka harian per halaman. IP dan user agent tidak disimpan di basis data. Data dihapus otomatis setelah {{ $masaSimpanBulan > 0 ? $masaSimpanBulan.' bulan' : '— (penghapusan otomatis tidak aktif)' }}. Bot, permintaan admin, dan aset tidak dihitung.</p>
    </div>
@endsection
