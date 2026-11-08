@props(['jenis' => 'berita', 'nama' => null]) {{-- jenis: komoditas | berita | umkm; nama: nama komoditas atau kategori berita --}}

{{--
    Sampul ilustrasi pengganti foto, dipakai sampai foto asli yang disetujui desa tersedia (bukan foto AI/stok).
    - komoditas: pemandangan ladang dengan tanaman sesuai jenisnya (jagung, padi, pisang, singkong; lainnya tunas).
    - berita: warna dan ikon sesuai kategori, dengan pola gelombang.
    - umkm: pola anyaman dengan ikon toko.
    Murni dekoratif (aria-hidden); judul kartu sudah menjelaskan isinya.
--}}
@php
    $uid = 'sp'.\Illuminate\Support\Str::random(6);
    $kunci = \Illuminate\Support\Str::lower(trim((string) $nama));

    $tanaman = match (true) {
        str_contains($kunci, 'jagung') => 'jagung',
        str_contains($kunci, 'padi') || str_contains($kunci, 'beras') || str_contains($kunci, 'sawah') => 'padi',
        str_contains($kunci, 'pisang') => 'pisang',
        str_contains($kunci, 'singkong') || str_contains($kunci, 'ubi') || str_contains($kunci, 'kasava') => 'singkong',
        default => 'tunas',
    };

    // Warna dan ikon sampul berita per kategori.
    [$warna, $lembut, $ikon] = match (true) {
        str_contains($kunci, 'tani') || str_contains($kunci, 'pertanian') => ['#c98a12', '#fff4d6', 'sprout'],
        str_contains($kunci, 'pemerintah') => ['#2d5ba8', '#e6eefb', 'building'],
        str_contains($kunci, 'umum') || str_contains($kunci, 'pengumuman') => ['#d9731a', '#fdeedd', 'message'],
        default => ['#1b5e20', '#e8f1e4', 'users'],
    };

    // [x, skala] barisan tanaman komoditas; pisang lebih sedikit karena lebar.
    $barisan = $tanaman === 'pisang'
        ? [[70, 0.95], [200, 1.1], [330, 0.9]]
        : [[34, 0.78], [104, 0.92], [180, 1.05], [258, 0.9], [332, 0.82], [396, 0.72]];
@endphp

<div {{ $attributes->class(['relative overflow-hidden']) }} aria-hidden="true">
    @if ($jenis === 'komoditas')
        <svg viewBox="0 0 400 190" preserveAspectRatio="xMidYMax slice" class="absolute inset-0 size-full transition-transform duration-500 group-hover:scale-105">
            <defs>
                <linearGradient id="{{ $uid }}-langit" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#e7f0dc" />
                    <stop offset="1" stop-color="#fbe9bd" />
                </linearGradient>

                {{-- Jagung --}}
                <symbol id="{{ $uid }}-jagung" viewBox="0 0 120 200" overflow="visible">
                    <path d="M60 200 C60 150 61 90 60 28" stroke="#1b5e20" stroke-width="5" stroke-linecap="round" fill="none" />
                    <path d="M60 160 C40 150 18 130 6 96 C26 120 44 136 60 146 Z" fill="#2e7d32" />
                    <path d="M60 128 C82 118 104 98 114 66 C96 92 78 106 60 114 Z" fill="#388e3c" />
                    <path d="M60 92 C44 82 28 64 22 38 C36 58 48 70 60 80 Z" fill="#43a047" />
                    <g transform="rotate(16 74 104)">
                        <ellipse cx="74" cy="104" rx="9" ry="24" fill="#f9a825" />
                        <path d="M74 80 C66 92 64 112 70 130 C62 120 60 98 66 84 Z" fill="#7cb342" />
                        <path d="M74 80 C82 92 84 112 78 130 C86 118 88 98 82 84 Z" fill="#689f38" />
                    </g>
                    <path d="M60 30 L50 6 M60 30 L60 2 M60 30 L70 6" stroke="#c9a227" stroke-width="2.4" stroke-linecap="round" />
                </symbol>

                {{-- Padi: tangkai melengkung dengan bulir berupa titik-titik --}}
                <symbol id="{{ $uid }}-padi" viewBox="0 0 120 200" overflow="visible">
                    <g fill="none" stroke-linecap="round">
                        <path d="M60 200 C56 150 44 110 22 96" stroke="#558b2f" stroke-width="3" />
                        <path d="M60 200 C64 140 80 104 104 92" stroke="#558b2f" stroke-width="3" />
                        <path d="M60 200 C60 140 58 90 52 50" stroke="#689f38" stroke-width="3.2" />
                        <path d="M60 200 C40 170 22 160 6 156" stroke="#7cb342" stroke-width="4" />
                        <path d="M60 200 C82 172 100 166 116 166" stroke="#7cb342" stroke-width="4" />
                        <path d="M52 50 C44 40 30 46 24 64 C20 76 18 86 16 96" stroke="#d9a62a" stroke-width="7" stroke-dasharray="0.1 8.5" />
                        <path d="M104 92 C112 80 112 66 104 58" stroke="#d9a62a" stroke-width="6.5" stroke-dasharray="0.1 8.5" />
                        <path d="M22 96 C12 92 8 104 10 116" stroke="#e0b23a" stroke-width="6.5" stroke-dasharray="0.1 8.5" />
                    </g>
                </symbol>

                {{-- Pisang --}}
                <symbol id="{{ $uid }}-pisang" viewBox="0 0 160 220" overflow="visible">
                    <path d="M74 220 L78 96 L86 96 L92 220 Z" fill="#7a8f3a" />
                    <path d="M82 98 C60 70 30 66 4 82 C30 76 56 84 80 104 Z" fill="#2e7d32" />
                    <path d="M82 98 C104 66 136 60 158 76 C134 72 108 82 86 104 Z" fill="#388e3c" />
                    <path d="M82 100 C70 62 76 28 98 6 C88 34 86 66 88 100 Z" fill="#43a047" />
                    <path d="M82 100 C62 74 50 46 56 14 C62 44 72 70 86 98 Z" fill="#558b2f" />
                    <path d="M82 102 C100 96 124 104 140 124 C120 112 102 108 84 110 Z" fill="#2e7d32" />
                    <g fill="#f9c22e" stroke="#c98a12" stroke-width="1.2">
                        <path d="M96 112 C112 112 118 124 114 136 C110 128 102 122 94 120 Z" />
                        <path d="M94 122 C110 124 114 138 108 148 C106 140 98 134 92 130 Z" />
                        <path d="M92 132 C106 136 108 150 100 158 C100 150 94 144 88 140 Z" />
                    </g>
                    <path d="M92 106 C94 120 94 140 90 160" stroke="#6d4c1f" stroke-width="2.4" fill="none" />
                </symbol>

                {{-- Singkong: batang kemerahan, daun menjari, umbi di pangkal --}}
                <symbol id="{{ $uid }}-singkong" viewBox="0 0 120 200" overflow="visible">
                    <path d="M60 200 C58 160 54 120 42 84 M58 160 C64 130 76 104 92 80" stroke="#8d4a2b" stroke-width="3.4" stroke-linecap="round" fill="none" />
                    @foreach ([[42, 80, -20], [92, 76, 15], [56, 118, -5]] as [$dx, $dy, $putar])
                        <g transform="translate({{ $dx }} {{ $dy }}) rotate({{ $putar }})" fill="#43a047">
                            @foreach ([-70, -35, 0, 35, 70] as $sudut)
                                <ellipse cx="0" cy="-17" rx="4.6" ry="17" transform="rotate({{ $sudut }})" fill="{{ $loop->even ? '#388e3c' : '#4caf50' }}" />
                            @endforeach
                        </g>
                    @endforeach
                    <g fill="#a0663a" stroke="#7a4a25" stroke-width="1">
                        <ellipse cx="44" cy="196" rx="20" ry="6" transform="rotate(-18 44 196)" />
                        <ellipse cx="76" cy="197" rx="19" ry="5.5" transform="rotate(14 76 197)" />
                    </g>
                </symbol>

                {{-- Tunas (komoditas lain) --}}
                <symbol id="{{ $uid }}-tunas" viewBox="0 0 120 200" overflow="visible">
                    <path d="M60 200 C60 170 60 150 60 120" stroke="#2e7d32" stroke-width="4" stroke-linecap="round" fill="none" />
                    <path d="M60 140 C40 140 24 124 22 100 C42 102 58 116 60 140 Z" fill="#43a047" />
                    <path d="M60 126 C80 126 96 108 98 84 C78 86 62 102 60 126 Z" fill="#388e3c" />
                </symbol>
            </defs>

            <rect width="400" height="190" fill="url(#{{ $uid }}-langit)" />
            <circle cx="322" cy="56" r="30" fill="#f9a825" opacity="0.28" />
            <circle cx="322" cy="56" r="19" fill="#f9a825" />
            <path d="M0 132 C70 104 150 112 220 120 C290 128 350 106 400 112 V190 H0 Z" fill="#b8cf96" />
            <path d="M0 158 C90 140 190 148 260 152 C330 156 370 146 400 148 V190 H0 Z" fill="#86ad5c" />
            <path d="M0 176 C120 166 260 170 400 172 V190 H0 Z" fill="#2e7d32" />

            @foreach ($barisan as [$x, $s])
                @php
                    [$w, $h] = $tanaman === 'pisang' ? [160, 220] : [120, 200];
                    $k = 0.56 * $s;
                @endphp
                <use href="#{{ $uid }}-{{ $tanaman }}" x="{{ $x - $w * $k / 2 }}" y="{{ 182 - $h * $k }}" width="{{ $w * $k }}" height="{{ $h * $k }}" />
            @endforeach
        </svg>
    @elseif ($jenis === 'umkm')
        <svg class="absolute inset-0 size-full" preserveAspectRatio="xMidYMid slice">
            <defs>
                <pattern id="{{ $uid }}-anyaman" width="24" height="24" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <rect width="24" height="24" fill="#f3dfb6" />
                    <rect x="0" y="0" width="12" height="12" fill="#e7c78a" />
                    <rect x="12" y="12" width="12" height="12" fill="#e7c78a" />
                    <path d="M0 4h12M0 8h12M12 16h12M12 20h12" stroke="#d4ab62" stroke-width="1" />
                    <path d="M16 0v12M20 0v12M4 12v12M8 12v12" stroke="#e2c286" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $uid }}-anyaman)" />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="flex size-16 items-center justify-center rounded-full bg-surface text-[#8a5a12] shadow-[0_8px_24px_rgb(138_90_18/0.25)] ring-4 ring-surface/60">
                <x-ui.icon name="store" class="size-8" />
            </span>
        </div>
    @else
        <div class="absolute inset-0" style="background: {{ $lembut }}"></div>
        <svg viewBox="0 0 400 220" preserveAspectRatio="xMidYMid slice" class="absolute inset-0 size-full transition-transform duration-500 group-hover:scale-105">
            <g fill="none" stroke="{{ $warna }}">
                @foreach (range(1, 9) as $i)
                    <circle cx="380" cy="210" r="{{ $i * 34 }}" stroke-opacity="{{ 0.22 - $i * 0.018 }}" stroke-width="1.5" />
                @endforeach
            </g>
            <g fill="{{ $warna }}" fill-opacity="0.18">
                @foreach (range(0, 4) as $bx)
                    @foreach (range(0, 3) as $by)
                        <circle cx="{{ 26 + $bx * 18 }}" cy="{{ 26 + $by * 18 }}" r="2" />
                    @endforeach
                @endforeach
            </g>
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="flex size-16 items-center justify-center rounded-full text-surface shadow-[0_10px_26px_rgb(0_0_0/0.18)] ring-[6px] ring-surface/70 transition-transform duration-300 group-hover:scale-110" style="background: {{ $warna }}">
                <x-ui.icon :name="$ikon" class="size-8" />
            </span>
        </div>
    @endif
</div>
