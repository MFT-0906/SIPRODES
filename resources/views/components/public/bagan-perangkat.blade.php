@props(['daftar' => []]) {{-- daftar: [['name', 'position', 'photo', 'photo_alt'], ...] dari KontenPublik::profil() --}}

{{--
    Bagan organisasi pemerintah desa, disusun dari nama jabatan:
    Kepala Desa di puncak; di bawahnya Sekretariat (Sekretaris Desa dan Kepala Urusan),
    Pelaksana Teknis (Kepala Seksi), dan Pelaksana Kewilayahan (Kepala Dusun) bila ada.
    Jabatan lain yang tidak dikenali tampil di kelompok "Perangkat lainnya".
--}}
@php
    $kelompok = ['puncak' => [], 'sekretaris' => [], 'urusan' => [], 'seksi' => [], 'dusun' => [], 'lainnya' => []];
    foreach ($daftar as $p) {
        $j = \Illuminate\Support\Str::lower($p['position']);
        $kunci = match (true) {
            str_starts_with($j, 'kepala desa') || $j === 'kades' => 'puncak',
            str_starts_with($j, 'sekretaris') || $j === 'sekdes' => 'sekretaris',
            str_starts_with($j, 'kaur') || str_starts_with($j, 'kepala urusan') => 'urusan',
            str_starts_with($j, 'kasi') || str_starts_with($j, 'kepala seksi') => 'seksi',
            str_starts_with($j, 'kadus') || str_starts_with($j, 'kepala dusun') => 'dusun',
            default => 'lainnya',
        };
        $kelompok[$kunci][] = $p;
    }

    // Kolom di bawah Kepala Desa; kolom kosong tidak ditampilkan.
    $kolom = array_values(array_filter([
        ['Sekretariat', 'Administrasi dan keuangan desa', array_merge($kelompok['sekretaris'], $kelompok['urusan'])],
        ['Pelaksana teknis', 'Pemerintahan, kesejahteraan, dan pelayanan', $kelompok['seksi']],
        ['Pelaksana kewilayahan', 'Kepala dusun', $kelompok['dusun']],
        ['Perangkat lainnya', null, $kelompok['lainnya']],
    ], fn ($k) => $k[2] !== []));

    $inisial = fn (string $nama) => collect(preg_split('/\s+/', trim($nama)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
@endphp

<div {{ $attributes->class(['bagan-perangkat flex flex-col items-center']) }}>
    {{-- Puncak: Kepala Desa --}}
    @foreach ($kelompok['puncak'] as $p)
        <div class="flex w-full max-w-[340px] items-center gap-4 rounded-[16px] bg-primary p-4 pr-5 text-surface shadow-[0_14px_34px_rgb(20_73_26/0.22)]">
            <span class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-surface/15 ring-2 ring-accent">
                @if ($p['photo'])
                    <img src="{{ $p['photo'] }}" alt="{{ $p['photo_alt'] }}" loading="lazy" class="size-full object-cover">
                @else
                    <span class="type-h3 text-surface" aria-hidden="true">{{ $inisial($p['name']) }}</span>
                @endif
            </span>
            <span class="flex min-w-0 flex-col">
                <span class="type-label-sm text-accent-soft">{{ $p['position'] }}</span>
                <span class="type-h3 text-surface">{{ $p['name'] }}</span>
            </span>
        </div>
    @endforeach

    @if ($kolom !== [])
        {{-- Garis penghubung dari puncak ke kolom-kolom di bawahnya --}}
        @if ($kelompok['puncak'] !== [])
            <span class="h-8 w-0.5 bg-border" aria-hidden="true"></span>
        @endif

        <div @class([
            'relative grid w-full gap-6 md:gap-5',
            'md:grid-cols-2' => count($kolom) === 2,
            'md:grid-cols-3' => count($kolom) >= 3,
        ])>
            {{-- Garis mendatar yang menyatukan kolom (layar lebar) --}}
            @if (count($kolom) > 1)
                <span class="absolute top-0 hidden h-0.5 bg-border md:block" style="left: calc(100% / {{ count($kolom) * 2 }}); right: calc(100% / {{ count($kolom) * 2 }})" aria-hidden="true"></span>
            @endif

            @foreach ($kolom as [$judul, $keterangan, $anggota])
                <div class="relative flex flex-col items-stretch md:pt-8">
                    <span class="absolute left-1/2 top-0 hidden h-8 w-0.5 -translate-x-1/2 bg-border md:block" aria-hidden="true"></span>
                    <div class="flex flex-col gap-3 rounded-[16px] border border-border bg-background p-4">
                        <div class="flex flex-col">
                            <h3 class="type-label text-primary">{{ $judul }}</h3>
                            @if ($keterangan)
                                <p class="type-body-sm text-muted">{{ $keterangan }}</p>
                            @endif
                        </div>
                        <ul class="flex flex-col gap-2">
                            @foreach ($anggota as $p)
                                <li class="flex items-center gap-3 rounded-[12px] border border-border bg-surface p-3">
                                    <span class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-primary">
                                        @if ($p['photo'])
                                            <img src="{{ $p['photo'] }}" alt="{{ $p['photo_alt'] }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <span class="type-label" aria-hidden="true">{{ $inisial($p['name']) }}</span>
                                        @endif
                                    </span>
                                    <span class="flex min-w-0 flex-col">
                                        <span class="type-label text-text">{{ $p['name'] }}</span>
                                        <span class="type-body-sm text-muted">{{ $p['position'] }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
