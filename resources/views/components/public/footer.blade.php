@php
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
    // Kontak resmi hanya tampil setelah diverifikasi pemerintah desa.
    $kontak = collect($identitas['kontak'])->filter(fn ($k) => $k['verified'] && filled($k['value']));
    $tautan = \App\Support\Navigation::footer();
@endphp

<footer class="on-dark bg-primary-dark text-primary-soft">
    <div class="container-page pb-8 pt-10 md:pt-14">
        <div class="grid gap-8 md:gap-16 {{ $kontak->isNotEmpty() ? 'md:grid-cols-3' : 'md:grid-cols-2' }}">
            <div class="flex flex-col gap-3">
                <p class="type-h3 text-surface md:type-h2">Desa {{ $identitas['nama'] }}</p>
                <p class="type-body-sm">{{ $identitas['wilayah'] }}</p>
            </div>

            @if ($kontak->isNotEmpty())
                <div class="flex flex-col gap-3">
                    <h2 class="type-label text-accent">Kontak Resmi</h2>
                    <ul class="flex flex-col gap-2">
                        @isset($kontak['alamat'])
                            <li class="flex items-start gap-2 type-body-sm">
                                <x-ui.icon name="map-pin" class="mt-0.5 size-4" />
                                <span>{{ $kontak['alamat']['value'] }}</span>
                            </li>
                        @endisset
                        @isset($kontak['telepon'])
                            <li class="flex items-start gap-2 type-body-sm">
                                <x-ui.icon name="phone" class="mt-0.5 size-4" />
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $kontak['telepon']['value']) }}" class="hover:underline">{{ $kontak['telepon']['value'] }}</a>
                            </li>
                        @endisset
                        @isset($kontak['email'])
                            <li class="flex items-start gap-2 type-body-sm">
                                <x-ui.icon name="mail" class="mt-0.5 size-4" />
                                <a href="mailto:{{ $kontak['email']['value'] }}" class="break-all hover:underline">{{ $kontak['email']['value'] }}</a>
                            </li>
                        @endisset
                    </ul>
                </div>
            @endif

            <nav aria-label="Tautan penting" class="flex flex-col gap-3">
                <h2 class="type-label text-accent">Tautan Penting</h2>
                <ul class="flex flex-col">
                    @foreach ($tautan as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="flex min-h-11 items-center type-body-sm hover:underline md:min-h-0 md:py-1.5">{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <hr class="my-8 border-primary">
        <p class="type-label-sm">© {{ date('Y') }} {{ $identitas['sebutan'] }} {{ $identitas['nama'] }}</p>
    </div>
</footer>
