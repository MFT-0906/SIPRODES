@extends('layouts.public')

@section('title', 'Berita & Agenda')
@section('description', 'Kabar terbaru kegiatan pemerintahan dan masyarakat, serta jadwal agenda mendatang Desa Marga Agung.')

@php
    use App\Support\Format;

    $saring = fn (?string $kategoriDipilih) => array_filter(['kategori' => $kategoriDipilih, 'q' => $kata]);
@endphp

@section('content')
    {{-- Judul halaman --}}
    <section class="bg-background">
        <div class="container-page flex flex-col gap-3 py-8 lg:py-10">
            <x-ui.breadcrumb :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => 'Berita & Agenda']]" />
            <h1 class="type-h1 text-text">Berita &amp; Agenda Desa</h1>
            <p class="type-body max-w-[760px] text-muted">Kabar terbaru kegiatan pemerintahan dan masyarakat, serta jadwal agenda mendatang Desa Marga Agung.</p>
        </div>
    </section>

    <div class="bg-surface">
        <div class="container-page flex flex-col gap-8 pb-14 pt-8 lg:gap-12 lg:pb-20 lg:pt-10">
            {{-- Filter kategori dan pencarian --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <nav aria-label="Kategori berita" class="-mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:px-0">
                    <x-ui.chip :href="route('berita.index', $saring(null))" :active="is_null($aktif)" class="shrink-0">Semua</x-ui.chip>
                    @foreach ($kategori as $nama)
                        <x-ui.chip :href="route('berita.index', $saring($nama))" :active="$aktif === $nama" class="shrink-0">{{ $nama }}</x-ui.chip>
                    @endforeach
                </nav>

                <form method="get" action="{{ route('berita.index') }}" role="search" class="lg:w-[320px]">
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

                    @if ($memuat)
                        {{-- Keadaan memuat: kerangka kartu (animasi mati bila reduced-motion). --}}
                        <div role="status" aria-busy="true" class="flex flex-col gap-6">
                            <p class="type-body-sm text-muted">Memuat berita…</p>
                            <div class="grid gap-4 md:grid-cols-2 md:gap-6">
                                @for ($i = 0; $i < 6; $i++)
                                    <x-ui.skeleton-card />
                                @endfor
                            </div>
                        </div>
                    @elseif ($berita->count())
                        <div class="grid gap-4 md:grid-cols-2 md:gap-6">
                            @foreach ($berita as $item)
                                <x-ui.card-news
                                    :title="$item['title']" :excerpt="$item['excerpt']" :date="Format::tanggal($item['tanggal'])"
                                    :category="$item['category']" :href="route('berita.show', $item['slug'])"
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
                <aside aria-labelledby="agenda-judul" class="flex flex-col gap-4">
                    <h2 id="agenda-judul" class="type-h2 text-text">Agenda desa</h2>

                    @forelse ($agendaMendatang as $agenda)
                        @php [$hari, $bulan] = Format::hariBulan($agenda['tanggal']); @endphp
                        <x-ui.card-agenda :day="$hari" :month="$bulan" :title="$agenda['title']" :time="$agenda['time']" :location="$agenda['location']" />
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
                                    <x-ui.card-agenda :day="$hari" :month="$bulan" :title="$agenda['title']" :time="$agenda['time']" :location="$agenda['location']" />
                                @endforeach
                            </div>
                        </details>
                    @endif
                </aside>
            </div>
        </div>
    </div>
@endsection
