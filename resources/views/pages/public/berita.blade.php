@extends('layouts.public')

@section('title', 'Berita & Agenda')
@section('description', 'Kabar terbaru kegiatan pemerintahan dan masyarakat, serta jadwal agenda mendatang Desa Marga Agung.')

@php
    use App\Support\Format;
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
            <form method="get" action="{{ route('berita.index') }}" role="search" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:gap-6">
                <div class="flex flex-col gap-3 w-full lg:w-[250px] lg:shrink-0">
                    <x-ui.select
                        name="kategori"
                        label="Kategori"
                        placeholder="Semua kategori"
                        :penanda="false"
                        :selected="$aktif"
                        :options="array_combine($kategori->all(), $kategori->all())"
                        x-on:change="$el.form.requestSubmit()"
                    />
                    <noscript><x-ui.button type="submit" class="mt-3 w-full">Tampilkan</x-ui.button></noscript>
                </div>
                <div class="flex flex-col gap-3 w-full lg:flex-1 lg:max-w-[320px]">
                    <x-ui.search name="q" label="Cari berita" placeholder="Cari berita…" :value="$kata" />
                </div>
            </form>

            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-8">
                {{-- Daftar berita --}}
                <section aria-labelledby="daftar-berita-judul" class="flex flex-col gap-6">
                    <h2 id="daftar-berita-judul" class="sr-only">Daftar berita</h2>

                    @if ($berita->count())
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
                {{-- Di layar lebar agenda ikut menempel saat daftar berita panjang digulir; tingginya dibatasi agar bagian bawahnya tetap terjangkau. --}}
                <aside aria-labelledby="agenda-judul" class="flex flex-col gap-4 lg:sticky lg:top-6 lg:-mx-2 lg:max-h-[calc(100vh-3rem)] lg:self-start lg:overflow-y-auto lg:px-2">
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
