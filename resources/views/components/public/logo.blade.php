@props(['tone' => 'gelap', 'caption' => null, 'href' => null]) {{-- gelap: untuk header hijau; terang: untuk latar terang; caption/href menggantikan teks "Pemerintah Desa" dan tautan beranda --}}

@php
    // Logo sementara (ikon pada kotak kuning). TODO: ganti dengan logo resmi desa setelah dikonfirmasi.
    $identitas = \App\Support\DemoContent::identitas();
    $gelap = $tone === 'gelap';
@endphp

<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['flex items-center gap-3 rounded-control']) }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-[10px] bg-accent text-text">
        <x-ui.icon name="sprout" class="size-6" />
    </span>
    <span class="flex flex-col leading-[1.4]">
        <span class="type-label-sm {{ $gelap ? 'text-primary-soft' : 'text-muted' }}">{{ $caption ?? $identitas['sebutan'] }}</span>
        <span class="type-h3 {{ $gelap ? 'text-surface' : 'text-text' }}">{{ $identitas['nama'] }}</span>
    </span>
</a>
