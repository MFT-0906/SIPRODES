@extends('layouts.public')

@php
    use App\Support\Format;

    $ringkasan = $data['ringkasan'];
    $pendapatan = $ringkasan['pendapatan'];
    $belanja = $ringkasan['belanja'];
    $adaDiagram = ! is_null($pendapatan) && ! is_null($belanja);
    $sumber = 'Sumber: APBDes '.$tahun;
@endphp

@section('title', 'Transparansi APBDes')
@section('description', $pengantar)

@section('content')
    {{-- Judul halaman --}}
    <x-public.kepala-halaman jejak="Transparansi APBDes" judul="Transparansi APBDes" :pengantar="$pengantar" />

    <div class="bg-surface">
        <div class="container-page flex flex-col gap-8 pb-14 pt-8 lg:pb-[72px] lg:pt-10">
            {{-- Pemilih tahun --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <form method="get" x-data class="md:hidden">
                    <x-ui.select
                        name="tahun" label="Tahun anggaran" :placeholder="null" :penanda="false" :selected="$tahun"
                        :options="array_combine($tahunTersedia, $tahunTersedia)"
                        x-on:change="$el.form.requestSubmit()"
                    />
                    <noscript><x-ui.button type="submit" class="mt-3 w-full">Tampilkan</x-ui.button></noscript>
                </form>

                <nav aria-label="Tahun anggaran" class="hidden gap-1 rounded-[10px] border border-border bg-surface p-1 md:flex">
                    @foreach ($tahunTersedia as $thn)
                        <a
                            href="{{ route('apbdes', ['tahun' => $thn]) }}"
                            @if ($thn === $tahun) aria-current="true" @endif
                            class="inline-flex min-h-11 items-center rounded-control px-5 py-2.5 type-label {{ $thn === $tahun ? 'bg-primary text-surface' : 'text-text hover:bg-primary-soft' }}"
                        >{{ $thn }}</a>
                    @endforeach
                </nav>

                <p class="flex items-center gap-2 type-body-sm text-muted">
                    <x-ui.icon name="calendar" class="size-4 shrink-0" />
                    Periode anggaran: {{ $data['periode'] }}
                </p>
            </div>

            {{-- Ringkasan --}}
            <section aria-labelledby="ringkasan-judul" class="flex flex-col gap-6">
                <h2 id="ringkasan-judul" class="sr-only">Ringkasan anggaran tahun {{ $tahun }}</h2>

                <div class="grid gap-4 md:grid-cols-3 lg:gap-6">
                    <x-ui.card-stat label="Total Pendapatan" :value="Format::rupiah($pendapatan)" :source="$sumber" icon="download" aksen="var(--color-primary)" />
                    <x-ui.card-stat label="Total Belanja" :value="Format::rupiah($belanja)" :source="$sumber" icon="upload" aksen="#c98a12" />
                    <x-ui.card-stat label="Pembiayaan Netto" :value="Format::rupiah($ringkasan['pembiayaan'])" :source="$sumber" icon="wallet" aksen="var(--color-grafik-6)" />
                </div>

                {{-- Diagram ringkas ditampilkan lebih dulu; tabel rinci di bawahnya tetap menjadi acuan utama. --}}
                @php
                    // Donat belanja per bidang: keliling lingkaran r = 15,915 tepat 100 satuan, jadi panjang busur = persen.
                    $barisBelanja = collect($data['belanja']['rows'])->filter(fn ($b) => ($b['anggaran'] ?? 0) > 0)->sortByDesc('anggaran')->values();
                    $totalBelanja = $barisBelanja->sum('anggaran');
                    $kumulatif = 0;
                    $segmen = $barisBelanja->map(function ($b, $i) use ($totalBelanja, &$kumulatif) {
                        $persen = $totalBelanja ? $b['anggaran'] / $totalBelanja * 100 : 0;
                        $item = [
                            'nama' => $b['uraian'],
                            'persen' => Format::persen($persen),
                            'nilai' => Format::rupiah($b['anggaran']),
                            'panjang' => round(max($persen - 0.8, 0.2), 3), // celah tipis antarbusur
                            'offset' => round(25 - $kumulatif, 3),          // mulai dari arah jam 12
                            'warna' => 'var(--color-grafik-'.(($i % 6) + 1).')',
                        ];
                        $kumulatif += $persen;

                        return $item;
                    });
                    $terbesar = $segmen->first();
                    $selisih = ! is_null($pendapatan) && ! is_null($belanja) ? $pendapatan - $belanja : null;
                @endphp

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-6">
                    {{-- Ke mana anggaran dibelanjakan --}}
                    <div class="flex flex-col gap-5 rounded-[14px] border border-border bg-surface p-4 md:p-6">
                        <div class="flex flex-col gap-1">
                            <h3 class="type-h3 text-text">Ke mana belanja desa dialokasikan?</h3>
                            @if ($terbesar)
                                <p class="type-body-sm text-muted">Porsi terbesar: <span class="type-label text-text">{{ $terbesar['nama'] }}</span> ({{ $terbesar['persen'] }}). Arahkan kursor atau ketuk bidang untuk melihat rinciannya.</p>
                            @endif
                        </div>

                        @if ($segmen->isNotEmpty())
                            <div
                                x-data="donatAnggaran(@js($segmen), @js(['nama' => 'Total belanja', 'persen' => '100%', 'nilai' => Format::rupiah($totalBelanja)]))"
                                x-muncul
                                class="flex flex-col items-center gap-6 md:flex-row md:items-center"
                            >
                                <div class="relative size-[220px] shrink-0 md:size-[240px]">
                                    <svg viewBox="0 0 42 42" class="size-full" aria-hidden="true">
                                        <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--color-background)" stroke-width="5" />
                                        @foreach ($segmen as $s)
                                            <circle
                                                class="busur-donat cursor-pointer"
                                                cx="21" cy="21" r="15.915" fill="none" stroke="{{ $s['warna'] }}"
                                                style="--urutan: {{ $loop->index }}; stroke-dasharray: {{ $s['panjang'] }} {{ 100 - $s['panjang'] }}; stroke-dashoffset: {{ $s['offset'] }}"
                                                x-bind:stroke-width="aktif === {{ $loop->index }} ? 6.4 : 5"
                                                x-bind:opacity="redup({{ $loop->index }}) ? 0.28 : 1"
                                                x-on:mouseenter="pilih({{ $loop->index }})" x-on:mouseleave="lepas()"
                                                x-on:click="aktif === {{ $loop->index }} ? lepas() : pilih({{ $loop->index }})"
                                            />
                                        @endforeach
                                    </svg>
                                    {{-- Teks tengah donat berubah mengikuti bidang yang disorot. --}}
                                    <div class="pointer-events-none absolute inset-[22%] flex flex-col items-center justify-center text-center" aria-live="polite">
                                        <span class="type-label-sm line-clamp-2 text-muted" x-text="sorot.nama">Total belanja</span>
                                        <span class="type-h2 tabular-nums text-text" x-text="sorot.persen">100%</span>
                                        <span class="type-label-sm tabular-nums text-primary" x-text="sorot.nilai">{{ Format::rupiah($totalBelanja) }}</span>
                                    </div>
                                </div>

                                <ul class="flex w-full min-w-0 flex-col gap-1">
                                    @foreach ($segmen as $s)
                                        <li class="bertahap" style="--urutan: {{ $loop->index + 2 }}">
                                            <button
                                                type="button"
                                                class="flex min-h-11 w-full items-center gap-3 rounded-control px-2.5 py-2 text-left transition-[background-color,opacity] hover:bg-background focus-visible:bg-background"
                                                x-bind:class="redup({{ $loop->index }}) && 'opacity-45'"
                                                x-bind:aria-pressed="aktif === {{ $loop->index }}"
                                                x-on:mouseenter="pilih({{ $loop->index }})" x-on:mouseleave="lepas()"
                                                x-on:focus="pilih({{ $loop->index }})" x-on:blur="lepas()"
                                            >
                                                <span class="size-3 shrink-0 rounded-[3px]" style="background: {{ $s['warna'] }}" aria-hidden="true"></span>
                                                <span class="min-w-0 flex-1 type-body-sm text-text">{{ $s['nama'] }}</span>
                                                <span class="type-label tabular-nums text-text">{{ $s['persen'] }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Pendapatan dibanding belanja --}}
                    <div class="flex flex-col gap-5 rounded-[14px] border border-border bg-surface p-4 md:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="type-h3 text-text">Pendapatan dan belanja</h3>
                            <ul class="flex items-center gap-4 type-body-sm text-muted">
                                <li class="flex items-center gap-1.5"><span class="size-3 rounded-[3px] bg-primary" aria-hidden="true"></span>Pendapatan</li>
                                <li class="flex items-center gap-1.5"><span class="size-3 rounded-[3px] bg-accent" aria-hidden="true"></span>Belanja</li>
                            </ul>
                        </div>

                        @if ($adaDiagram)
                            @php $maks = max($pendapatan, $belanja, 1); @endphp
                            <div
                                x-data x-muncul
                                role="img"
                                aria-label="Pendapatan {{ Format::rupiah($pendapatan) }}, belanja {{ Format::rupiah($belanja) }}. Rincian ada pada tabel di atas."
                                class="flex flex-1 flex-col gap-5"
                            >
                                @foreach ([['Pendapatan', $pendapatan, 'bg-primary'], ['Belanja', $belanja, 'bg-accent']] as [$label, $nilai, $warna])
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between type-body-sm text-text">
                                            <span>{{ $label }}</span>
                                            <span class="type-label tabular-nums">{{ Format::rupiah($nilai) }}</span>
                                        </div>
                                        <div class="h-8 overflow-hidden rounded-[8px] bg-background">
                                            <div class="batang-tumbuh h-8 rounded-[8px] {{ $warna }}" style="--urutan: {{ $loop->index }}; width: {{ round($nilai / $maks * 100, 1) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach

                                @php
                                    $barisPendapatan = collect($data['pendapatan']['rows'])->filter(fn ($b) => ($b['anggaran'] ?? 0) > 0)->sortByDesc('anggaran')->values();
                                    $totalPendapatan = max($barisPendapatan->sum('anggaran'), 1);
                                @endphp
                                @if ($barisPendapatan->isNotEmpty())
                                    {{-- Sumber pendapatan sebagai satu batang bersusun. --}}
                                    <div class="flex flex-col gap-2.5 border-t border-border pt-4">
                                        <p class="type-label text-text">Sumber pendapatan</p>
                                        <div class="batang-tumbuh flex h-4 overflow-hidden rounded-pill" style="--urutan: 2" aria-hidden="true">
                                            @foreach ($barisPendapatan as $b)
                                                <span class="h-full border-r-2 border-surface last:border-r-0" style="width: {{ round($b['anggaran'] / $totalPendapatan * 100, 2) }}%; background: var(--color-grafik-{{ ($loop->index % 6) + 1 }})" title="{{ $b['uraian'] }}"></span>
                                            @endforeach
                                        </div>
                                        <ul class="flex flex-wrap gap-x-4 gap-y-1.5">
                                            @foreach ($barisPendapatan as $b)
                                                <li class="flex items-center gap-1.5 type-label-sm text-muted">
                                                    <span class="size-2.5 rounded-[2px]" style="background: var(--color-grafik-{{ ($loop->index % 6) + 1 }})" aria-hidden="true"></span>
                                                    {{ $b['uraian'] }} <span class="tabular-nums text-text">{{ Format::persen($b['anggaran'] / $totalPendapatan * 100) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (! is_null($selisih))
                                    <div class="bertahap mt-auto flex items-start gap-3 rounded-[10px] {{ $selisih >= 0 ? 'bg-success-soft' : 'bg-warning-soft' }} p-4" style="--urutan: 4">
                                        <x-ui.icon name="{{ $selisih >= 0 ? 'check' : 'alert' }}" class="mt-0.5 size-5 shrink-0 {{ $selisih >= 0 ? 'text-success' : 'text-warning' }}" />
                                        <p class="type-body-sm text-text">
                                            @if ($selisih >= 0)
                                                Pendapatan melebihi belanja sebesar <span class="type-label tabular-nums">{{ Format::rupiah($selisih) }}</span> ({{ Format::persen($belanja / max($pendapatan, 1) * 100) }} pendapatan dibelanjakan).
                                            @else
                                                Belanja melebihi pendapatan sebesar <span class="type-label tabular-nums">{{ Format::rupiah(abs($selisih)) }}</span>, ditutup melalui pembiayaan.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex h-[200px] flex-col items-center justify-center gap-2 rounded-[10px] border-[1.5px] border-dashed border-border bg-background text-muted">
                                <x-ui.icon name="chart" class="size-7" />
                                <p class="type-body-sm">Diagram belum tersedia</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Rincian lengkap --}}
                <div class="flex flex-col gap-4 pt-2">
                    <h3 class="type-h2 text-text">Rincian anggaran</h3>
                    <div class="grid gap-4 lg:grid-cols-2 lg:items-start lg:gap-6">
                        <x-public.tabel-anggaran
                            id="tabel-pendapatan" judul="Pendapatan desa" total-label="Jumlah Pendapatan"
                            :rows="$data['pendapatan']['rows']" :total="$data['pendapatan']['total']"
                        />
                        <x-public.tabel-anggaran
                            id="tabel-belanja" judul="Belanja desa per bidang" total-label="Jumlah Belanja"
                            :rows="$data['belanja']['rows']" :total="$data['belanja']['total']"
                        />
                    </div>
                </div>
            </section>

            {{-- Arsip dokumen --}}
            <section aria-labelledby="arsip-judul" class="flex flex-col gap-4">
                <h2 id="arsip-judul" class="type-h2 text-text">Arsip dokumen APBDes</h2>
                <p class="type-body text-muted">Unduh dokumen resmi dalam format PDF. Pilih tahun di atas untuk melihat arsip tahun lain.</p>

                @forelse ($data['dokumen'] as $dokumen)
                    <article class="flex flex-col gap-3 rounded-[12px] border border-border bg-surface px-5 py-4 sm:flex-row sm:items-center sm:gap-4">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-[10px] bg-danger-soft text-danger">
                                <x-ui.icon name="file" class="size-[22px]" />
                            </span>
                            <div class="flex min-w-0 flex-col gap-1">
                                <h3 class="type-label text-text">{{ $dokumen['judul'] }}</h3>
                                <p class="type-body-sm text-muted">
                                    {{ collect([
                                        $dokumen['periode'] ? 'Periode: '.$dokumen['periode'] : null,
                                        'PDF',
                                        $dokumen['ukuran'] ? Format::ukuranBerkas($dokumen['ukuran']) : null,
                                        $dokumen['diunggah'] ? 'Diunggah '.Format::tanggal($dokumen['diunggah']) : null,
                                        $dokumen['url'] ? null : 'Berkas belum diunggah',
                                    ])->filter()->implode(' · ') }}
                                </p>
                            </div>
                        </div>

                        <x-ui.button
                            variant="secondary" size="s" icon="download"
                            :href="$dokumen['url']" :disabled="is_null($dokumen['url'])"
                            class="shrink-0 max-sm:w-full"
                        >Unduh PDF<span class="sr-only">: {{ $dokumen['judul'] }}</span></x-ui.button>
                    </article>
                @empty
                    <x-ui.empty-state
                        heading="h3" icon="file" ringkas
                        :title="'Dokumen APBDes tahun '.$tahun.' belum tersedia'"
                        description="Dokumen akan ditampilkan setelah diunggah oleh perangkat desa."
                    />
                @endforelse
            </section>

            {{-- Penjelasan awam --}}
            <section aria-labelledby="arti-judul" class="flex flex-col gap-6 rounded-card bg-background p-6 md:p-10 lg:flex-row lg:gap-8">
                <div class="flex flex-1 flex-col gap-3">
                    <h2 id="arti-judul" class="type-h2 text-text">Apa arti angka ini?</h2>
                    <p class="type-body text-text">Pendapatan adalah dana yang diterima desa. Belanja adalah rencana penggunaan dana untuk pelayanan dan kegiatan masyarakat.</p>
                    <p class="type-body text-muted">Ringkasan ini disusun agar mudah dibaca. Rincian kegiatan tercantum dalam dokumen APBDes yang dapat diunduh di atas.</p>
                </div>
                <div class="flex flex-col items-start gap-3 rounded-card border border-border bg-surface p-6 lg:w-[440px] lg:shrink-0 lg:p-7">
                    <x-ui.icon name="message" class="size-6 text-primary" />
                    <h3 class="type-h3 text-text">Ada pertanyaan tentang anggaran?</h3>
                    <p class="type-body text-muted">Sampaikan pertanyaan atau saran Anda melalui Ruang Aspirasi.</p>
                    <x-ui.button :href="route('aspirasi.create')" variant="secondary" icon="arrow-right">Buka Ruang Aspirasi</x-ui.button>
                </div>
            </section>
        </div>
    </div>
@endsection
