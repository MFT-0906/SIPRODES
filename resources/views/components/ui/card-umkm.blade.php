@props(['name', 'products', 'description', 'image' => null, 'imageAlt' => null, 'whatsapp' => null, 'whatsappApproved' => false])

@php
    throw_if(app()->isLocal() && $image && is_null($imageAlt), \InvalidArgumentException::class, 'x-ui.card-umkm: imageAlt wajib diisi saat image dipakai.');

    // Tombol WhatsApp hanya tampil jika nomor sudah disetujui pemiliknya untuk dipublikasikan.
    $digits = preg_replace('/\D+/', '', (string) $whatsapp);
    if (str_starts_with($digits, '0')) {
        $digits = '62'.substr($digits, 1);
    }
    $showWhatsapp = $whatsappApproved && $digits !== '';
@endphp

<article {{ $attributes->class(['flex flex-col overflow-hidden rounded-card border border-border bg-surface']) }}>
    @if ($image)
        <img src="{{ $image }}" alt="{{ $imageAlt }}" loading="lazy" class="h-[170px] w-full object-cover">
    @else
        <x-ui.sampul jenis="umkm" :nama="$name" class="h-[170px] w-full" />
    @endif

    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <h3 class="type-h3 text-text">{{ $name }}</h3>
        <p class="type-label text-primary">Produk: {{ $products }}</p>
        <p class="type-body-sm text-muted">{{ $description }}</p>

        @if ($showWhatsapp)
            <a
                href="https://wa.me/{{ $digits }}" target="_blank" rel="noopener noreferrer"
                class="mt-auto flex min-h-11 items-center justify-center gap-2 rounded-control border border-success bg-success-soft px-3.5 py-2.5 type-label text-success hover:bg-surface"
            >
                <x-ui.icon name="whatsapp" class="size-[18px]" />
                Hubungi via WhatsApp
                <span class="sr-only">(membuka tab baru)</span>
            </a>
        @endif
    </div>
</article>
