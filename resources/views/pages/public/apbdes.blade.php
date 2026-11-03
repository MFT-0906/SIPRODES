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
    <section class="bg-background">
        <div class="container-page flex flex-col gap-3 py-8 lg:py-10">
            <x-ui.breadcrumb :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => 'Transparansi APBDes']]" />
            <h1 class="type-h1 text-text">Transparansi APBDes</h1>
            <p class="type-body max-w-[760px] text-muted">{{ $pengantar }}</p>
        </div>
    </section>

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

            @unless ($data['resmi'])
                <x-ui.alert tone="peringatan" title="Data contoh">
                    Angka “Rp —” akan diganti dengan nilai resmi dari dokumen APBDes yang telah ditetapkan. Diagram tampil setelah angka diinput.
                </x-ui.alert>
            @endunless

            {{-- Ringkasan --}}
            <section aria-labelledby="ringkasan-judul" class="flex flex-col gap-6">
                <h2 id="ringkasan-judul" class="sr-only">Ringkasan anggaran tahun {{ $tahun }}</h2>

                <div class="grid gap-4 md:grid-cols-3 lg:gap-6">
                    <x-ui.card-stat label="Total Pendapatan" :value="Format::rupiah($pendapatan)" :source="$sumber" />
                    <x-ui.card-stat label="Total Belanja" :value="Format::rupiah($belanja)" :source="$sumber" />
                    <x-ui.card-stat label="Pembiayaan Netto" :value="Format::rupiah($ringkasan['pembiayaan'])" :source="$sumber" />
                </div>

                <div class="grid gap-4 lg:grid-cols-2 lg:gap-6">
                    <x-public.tabel-anggaran
                        id="tabel-pendapatan" judul="Pendapatan desa" total-label="Jumlah Pendapatan"
                        :rows="$data['pendapatan']['rows']" :total="$data['pendapatan']['total']"
                    />
                    <x-public.tabel-anggaran
                        id="tabel-belanja" judul="Belanja desa per bidang" total-label="Jumlah Belanja"
                        :rows="$data['belanja']['rows']" :total="$data['belanja']['total']"
                    />
                </div>

                {{-- Diagram pendamping; tabel di atas tetap menjadi acuan utama. --}}
                <div class="flex flex-col gap-4 rounded-[14px] border border-border bg-surface p-4 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="type-h3 text-text">Perbandingan pendapatan dan belanja</h3>
                        <ul class="flex items-center gap-4 type-body-sm text-muted">
                            <li class="flex items-center gap-1.5"><span class="size-3 rounded-[3px] bg-primary" aria-hidden="true"></span>Pendapatan</li>
                            <li class="flex items-center gap-1.5"><span class="size-3 rounded-[3px] bg-accent" aria-hidden="true"></span>Belanja</li>
                        </ul>
                    </div>

                    @if ($adaDiagram)
                        @php $maks = max($pendapatan, $belanja, 1); @endphp
                        <div
                            role="img"
                            aria-label="Pendapatan {{ Format::rupiah($pendapatan) }}, belanja {{ Format::rupiah($belanja) }}. Rincian ada pada tabel di atas."
                            class="flex flex-col gap-4"
                        >
                            @foreach ([['Pendapatan', $pendapatan, 'bg-primary'], ['Belanja', $belanja, 'bg-accent']] as [$label, $nilai, $warna])
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center justify-between type-body-sm text-text">
                                        <span>{{ $label }}</span>
                                        <span class="type-label">{{ Format::rupiah($nilai) }}</span>
                                    </div>
                                    <div class="h-6 rounded-[6px] bg-background">
                                        <div class="h-6 rounded-[6px] {{ $warna }}" style="width: {{ round($nilai / $maks * 100, 1) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex h-[200px] flex-col items-center justify-center gap-2 rounded-[10px] border-[1.5px] border-dashed border-border bg-background text-muted">
                            <x-ui.icon name="chart" class="size-7" />
                            <p class="type-body-sm">Diagram belum tersedia</p>
                        </div>
                    @endif
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
                        heading="h3" icon="file"
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
