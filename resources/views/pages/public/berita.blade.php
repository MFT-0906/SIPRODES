@extends('layouts.public')

@section('title', 'Berita & Agenda')
@section('description', 'Kabar terbaru kegiatan pemerintahan dan masyarakat, serta jadwal agenda mendatang Desa Marga Agung.')

@php
    use App\Support\Format;
@endphp

@section('content')
    {{-- Judul halaman --}}
    <x-public.kepala-halaman jejak="Berita & Agenda" judul="Berita & Agenda Desa" pengantar="Kabar terbaru kegiatan pemerintahan dan masyarakat, serta jadwal agenda mendatang Desa Marga Agung." />

    <div class="bg-surface">
        <div class="container-page flex flex-col gap-8 pb-14 pt-8 lg:gap-12 lg:pb-20 lg:pt-10">
            {{-- Filter kategori (tombol chip) dan pencarian --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-6">
                <nav aria-label="Kategori berita" class="-mx-4 overflow-x-auto px-4 lg:mx-0 lg:px-0">
                    <ul class="flex w-max items-center gap-2">
                        @php
                            $tautanKategori = fn (?string $k) => route('berita.index', array_filter(['kategori' => $k, 'q' => $kata !== '' ? $kata : null]));
                        @endphp
                        <li>
                            <a href="{{ $tautanKategori(null) }}" @if (! $aktif) aria-current="page" @endif
                               @class([
                                   'inline-flex min-h-11 items-center rounded-pill border px-4 type-label transition-colors',
                                   'border-primary bg-primary text-surface' => ! $aktif,
                                   'border-border bg-surface text-text hover:border-primary hover:text-primary' => $aktif,
                               ])>Semua</a>
                        </li>
                        @foreach ($kategori as $k)
                            @php $w = \App\Support\WarnaKategori::untuk($k); $pilih = $aktif === $k; @endphp
                            <li>
                                <a href="{{ $tautanKategori($k) }}" @if ($pilih) aria-current="page" @endif
                                   class="inline-flex min-h-11 items-center gap-2 rounded-pill border px-4 type-label transition-colors {{ $pilih ? 'text-surface' : 'border-border bg-surface text-text hover:border-primary' }}"
                                   @if ($pilih) style="background: {{ $w['warna'] }}; border-color: {{ $w['warna'] }}" @endif>
                                    <span class="size-2.5 rounded-full {{ $pilih ? 'bg-surface' : '' }}" @unless ($pilih) style="background: {{ $w['warna'] }}" @endunless aria-hidden="true"></span>
                                    {{ $k }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <form method="get" action="{{ route('berita.index') }}" role="search" class="w-full lg:w-[320px] lg:shrink-0">
                    @if ($aktif)
                        <input type="hidden" name="kategori" value="{{ $aktif }}">
                    @endif
                    <x-ui.search name="q" label="Cari berita" placeholder="Cari berita…" :value="$kata" />
                </form>
            </div>

            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-8">
                {{-- Daftar berita --}}
                <section aria-labelledby="daftar-berita-judul" class="flex flex-col gap-6">
                    <h2 id="daftar-berita-judul" class="sr-only">Daftar berita</h2>

                    @if ($berita->count())
                        @php
                            // Berita terbaru tampil besar hanya di halaman pertama tanpa filter atau pencarian.
                            $adaUtama = $berita->onFirstPage() && ! $aktif && $kata === '';
                        @endphp
                        <div class="grid gap-4 md:grid-cols-2 md:gap-6">
                            @foreach ($berita as $item)
                                <x-ui.card-news
                                    :title="$item['title']" :excerpt="$item['excerpt']" :date="Format::tanggal($item['tanggal'])"
                                    :category="$item['category']" :href="route('berita.show', $item['slug'])"
                                    :utama="$adaUtama && $loop->first"
                                    :class="$adaUtama && $loop->first ? 'md:col-span-2' : ''"
                                />
                            @endforeach
                        </div>
                        <x-ui.pagination :paginator="$berita" class="lg:self-start" />
                    @elseif (! $adaBerita)
                        <x-ui.empty-state title="Belum ada berita yang dipublikasikan" description="Berita akan tampil di sini setelah dipublikasikan oleh perangkat desa. Sementara itu, Anda dapat melihat agenda atau profil desa.">
                            <x-slot:action>
                                <x-ui.button :href="route('profil')" variant="secondary">Lihat Profil Desa</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state icon="search" title="Tidak ada berita yang cocok" description="Coba kata kunci lain atau hapus filter kategori.">
                            <x-slot:action>
                                <x-ui.button :href="route('berita.index')" variant="secondary">Hapus filter</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @endif
                </section>

                {{-- Agenda --}}
                {{-- Di layar lebar agenda ikut menempel (di bawah header) saat daftar berita panjang digulir; tingginya dibatasi agar bagian bawahnya tetap terjangkau. --}}
                <aside aria-labelledby="agenda-judul" class="flex flex-col gap-4 lg:sticky lg:top-24 lg:-mx-2 lg:max-h-[calc(100vh-7rem)] lg:self-start lg:overflow-y-auto lg:px-2">
                    <h2 id="agenda-judul" class="type-h2 text-text">Agenda desa</h2>

                    @forelse ($agendaMendatang as $agenda)
                        @php [$hari, $bulan] = Format::hariBulan($agenda['tanggal']); @endphp
                        <x-ui.card-agenda :day="$hari" :month="$bulan" :title="$agenda['title']" :time="$agenda['time']" :location="$agenda['location']" :relatif="$agenda['relatif'] ?? null" />
                    @empty
                        <p class="type-body text-muted">Belum ada agenda mendatang.</p>
                    @endforelse

                    @if ($agendaLewat)
                        <details class="group rounded-[12px] border border-border bg-surface">
                            <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 type-label text-muted [&::-webkit-details-marker]:hidden">
                                Agenda yang telah lewat
                                <x-ui.icon name="chevron-down" class="size-[18px] transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="flex flex-col gap-3 p-4 pt-1">
                                @foreach ($agendaLewat as $agenda)
                                    @php [$hari, $bulan] = Format::hariBulan($agenda['tanggal']); @endphp
                                    <x-ui.card-agenda :day="$hari" :month="$bulan" :title="$agenda['title']" :time="$agenda['time']" :location="$agenda['location']" :relatif="$agenda['relatif'] ?? null" />
                                @endforeach
                            </div>
                        </details>
                    @endif
                </aside>
            </div>
        </div>
    </div>
@endsection
