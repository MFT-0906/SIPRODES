@props(['tone' => 'gelap', 'caption' => null, 'href' => null, 'tanpaCaption' => false]) {{-- gelap: untuk latar hijau; terang: untuk latar terang; adaptif: mengikuti header publik (putih di atas foto, gelap di header putih); caption/href menggantikan teks "Pemerintah Desa" dan tautan beranda; tanpaCaption: hanya satu baris "Desa {nama}" --}}

@php
    // Desa belum memiliki logo resmi, jadi identitas ditampilkan sebagai tulisan: garis aksen kuning,
    // sebutan kecil "Pemerintah Desa", dan nama desa. Bila kelak ada logo resmi, letakkan di sini.
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
    $gelap = $tone === 'gelap';
    $adaptif = $tone === 'adaptif';

    $warnaSebutan = match (true) {
        $adaptif => 'text-primary transition-colors group-data-[terang=true]/header:text-accent-soft',
        $gelap => 'text-accent-soft',
        default => 'text-primary',
    };
    $warnaNama = match (true) {
        $adaptif => 'text-text transition-colors group-data-[terang=true]/header:text-surface',
        $gelap => 'text-surface',
        default => 'text-text',
    };
@endphp

<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['flex items-center gap-3 rounded-control']) }}>
    <span class="h-10 w-1 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
    <span class="flex flex-col leading-[1.25]">
        @unless ($tanpaCaption)
            <span class="text-[11px] font-semibold uppercase tracking-[0.14em] {{ $warnaSebutan }}">{{ $caption ?? $identitas['sebutan'] }}</span>
        @endunless
        <span class="whitespace-nowrap type-h3 {{ $warnaNama }}">{{ $tanpaCaption ? 'Desa '.$identitas['nama'] : $identitas['nama'] }}</span>
    </span>
</a>
