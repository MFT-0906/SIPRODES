@props(['tone' => 'gelap', 'caption' => null, 'href' => null, 'tanpaCaption' => false]) {{-- gelap: untuk latar hijau; terang: untuk latar terang; adaptif: mengikuti header publik (putih di atas foto beranda, gelap di header putih); caption/href menggantikan teks "Pemerintah Desa" dan tautan beranda; tanpaCaption: hanya satu baris "Desa {nama}" --}}

@php
    // Logo sementara (ikon pada kotak kuning). TODO: ganti dengan logo resmi desa setelah dikonfirmasi.
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
    $gelap = $tone === 'gelap';
    $adaptif = $tone === 'adaptif';
@endphp

<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['flex items-center gap-3 rounded-control']) }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-[10px] bg-accent text-text">
        <x-ui.icon name="sprout" class="size-6" />
    </span>
    <span class="flex flex-col leading-[1.4]">
        @unless ($tanpaCaption)
            <span class="type-label-sm {{ $adaptif ? 'text-muted transition-colors group-data-[terang=true]/header:text-primary-soft' : ($gelap ? 'text-primary-soft' : 'text-muted') }}">{{ $caption ?? $identitas['sebutan'] }}</span>
        @endunless
        <span class="whitespace-nowrap type-h3 {{ $adaptif ? 'text-text transition-colors group-data-[terang=true]/header:text-surface' : ($gelap ? 'text-surface' : 'text-text') }}">{{ $tanpaCaption ? 'Desa '.$identitas['nama'] : $identitas['nama'] }}</span>
    </span>
</a>
