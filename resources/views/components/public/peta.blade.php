@props(['peta', 'tinggi' => 'h-[300px] md:h-[380px]'])

{{-- Peta lokasi desa (Leaflet: OpenStreetMap + satelit Esri). Hanya tampil setelah lokasi diverifikasi; sebelum itu tampil penampung berisi keterangan. --}}
@if ($peta['lat'] ?? null)
    <figure {{ $attributes->class(['flex flex-col gap-2']) }}>
        <div
            x-data="petaDesa({{ Js::from(['lat' => $peta['lat'], 'lng' => $peta['lng'], 'zoom' => 17, 'judul' => 'Kantor Desa Marga Agung']) }})"
            class="relative isolate overflow-hidden rounded-card border border-border bg-primary-soft {{ $tinggi }}"
        >
            <div
                x-ref="peta" role="region" aria-label="Peta lokasi kantor Desa Marga Agung. Gunakan panah untuk menggeser, plus dan minus untuk memperbesar."
                class="size-full"
            ></div>

            {{-- Tampil bila JavaScript mati atau peta gagal dimuat; alamat tertulis dan tautan peta tetap tersedia. --}}
            <div x-show="gagal" x-cloak class="absolute inset-0 z-[1000] flex flex-col items-center justify-center gap-2 bg-primary-soft px-6 text-center text-primary">
                <x-ui.icon name="map-pin" class="size-8" />
                <p class="type-label">Peta tidak dapat dimuat</p>
                <a href="{{ $peta['osm_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center type-label underline">Buka peta di OpenStreetMap<span class="sr-only"> (membuka tab baru)</span></a>
            </div>
            <noscript>
                <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-primary-soft px-6 text-center text-primary">
                    <p class="type-label">Peta interaktif membutuhkan JavaScript.</p>
                    <a href="{{ $peta['osm_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center type-label underline">Buka peta di OpenStreetMap</a>
                </div>
            </noscript>
        </div>
        <figcaption class="flex flex-wrap items-center justify-between gap-2 type-label-sm text-muted">
            <span>Pilih “Peta” atau “Satelit” di pojok peta. Klik peta lalu gunakan roda mouse untuk memperbesar.</span>
            <a href="{{ $peta['osm_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-primary hover:underline">Lihat peta lebih besar<span class="sr-only"> (membuka tab baru)</span></a>
        </figcaption>
    </figure>
@else
    <div {{ $attributes->class(['flex flex-col items-center justify-center gap-2 rounded-card border border-border bg-primary-soft px-6 text-center text-primary', $tinggi]) }}>
        <x-ui.icon name="map-pin" class="size-8" />
        <p class="type-label">Peta lokasi desa</p>
        <p class="type-body-sm max-w-xs text-muted">Peta akan ditampilkan setelah lokasi diverifikasi oleh pemerintah desa. Alamat tertulis tetap tersedia.</p>
    </div>
@endif
