@props(['judul', 'jejak', 'pengantar' => null]) {{-- jejak: label halaman ini untuk jejak halaman (breadcrumb) --}}

{{--
    Bagian judul halaman publik, senada dengan hero beranda: latar hijau dengan foto desa
    (public/images/hero-desa.*) bila ada, judul putih, dan jejak halaman.
--}}
@php $foto = \App\Support\KontenPublik::fotoHero()['foto']; @endphp

<section {{ $attributes->class(['on-dark relative isolate overflow-hidden bg-primary-dark text-surface']) }}>
    @if ($foto)
        <img src="{{ $foto }}" alt="" aria-hidden="true" loading="lazy" class="absolute inset-0 -z-20 size-full scale-105 object-cover object-[center_60%] blur-[1px]">
    @endif
    <div @class([
        'absolute inset-0 -z-10',
        'bg-[linear-gradient(180deg,#14491a_0%,#0f3a14_100%)]' => ! $foto,
        'bg-[linear-gradient(180deg,rgb(20_73_26/0.82)_0%,rgb(20_73_26/0.74)_100%)] md:bg-[linear-gradient(90deg,rgb(20_73_26/0.88)_0%,rgb(27_94_32/0.66)_55%,rgb(27_94_32/0.38)_100%)]' => $foto,
    ])></div>

    <div class="pembuka container-page flex flex-col gap-3 py-10 [text-shadow:0_1px_14px_rgb(0_0_0/0.28)] lg:gap-4 lg:py-14">
        <x-ui.breadcrumb gelap :items="[['label' => 'Beranda', 'href' => route('home')], ['label' => $jejak]]" style="--urutan: 0" />
        <h1 class="type-h1 text-surface" style="--urutan: 1">{{ $judul }}</h1>
        @if ($pengantar)
            <p class="type-body max-w-[760px] text-surface/85 lg:type-body-lg" style="--urutan: 2">{{ $pengantar }}</p>
        @endif
    </div>
</section>
