{{--
    Ilustrasi ladang jagung untuk hero beranda, dipakai selama foto asli desa belum tersedia.
    Gambar vektor (bukan foto stok/AI), sehingga tidak menyesatkan sebagai foto desa sebenarnya.
    Gerak: matahari terbit dan lapisan bukit naik sekali saat halaman dibuka; batang jagung bergoyang pelan.
    Semua gerak berhenti bila pengguna memilih "kurangi gerak".
--}}
@php
    // [x, y, skala, lama goyang, tunda] — tunda negatif agar tiap batang sudah berada di fase berbeda sejak awal.
    $batangDepan = [
        [-10, 250, 1.05, '4.6s', '-1.2s'], [48, 262, 0.92, '3.9s', '-2.6s'], [104, 248, 1.12, '5.1s', '-0.4s'],
        [166, 266, 0.86, '4.2s', '-3.1s'], [222, 252, 1.0, '4.8s', '-1.9s'], [470, 256, 0.98, '4.4s', '-2.2s'],
        [528, 246, 1.1, '5.0s', '-0.8s'], [584, 262, 0.9, '3.8s', '-3.4s'],
    ];
    $batangTengah = [
        [262, 236, 0.52, '4.9s', '-1.4s'], [300, 240, 0.48, '4.1s', '-2.9s'], [338, 234, 0.55, '5.3s', '-0.6s'],
        [376, 240, 0.5, '4.5s', '-2.1s'], [414, 236, 0.53, '4.0s', '-3.6s'],
    ];
@endphp

<div {{ $attributes->class(['ladang relative overflow-hidden']) }}>
    <svg
        viewBox="0 0 640 420" preserveAspectRatio="xMidYMax slice" class="absolute inset-0 size-full"
        role="img" aria-label="Ilustrasi ladang jagung saat matahari terbit"
    >
        <defs>
            <linearGradient id="ladang-langit" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#e7f0dc" />
                <stop offset="0.55" stop-color="#fdf3dc" />
                <stop offset="1" stop-color="#fbe2a6" />
            </linearGradient>
            <linearGradient id="ladang-tanah" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#2e7d32" />
                <stop offset="1" stop-color="#14491a" />
            </linearGradient>

            {{-- Satu batang jagung: titik tumpu di bawah tengah (60, 200). --}}
            <symbol id="ladang-jagung" viewBox="0 0 120 200" overflow="visible">
                <path d="M60 200 C60 150 61 90 60 28" stroke="#1b5e20" stroke-width="5" stroke-linecap="round" fill="none" />
                <path d="M60 160 C40 150 18 130 6 96 C26 120 44 136 60 146 Z" fill="#2e7d32" />
                <path d="M60 128 C82 118 104 98 114 66 C96 92 78 106 60 114 Z" fill="#388e3c" />
                <path d="M60 92 C44 82 28 64 22 38 C36 58 48 70 60 80 Z" fill="#43a047" />
                <path d="M60 70 C72 60 84 46 90 26 C80 42 70 52 60 60 Z" fill="#2e7d32" />
                <g transform="rotate(16 74 104)">
                    <ellipse cx="74" cy="104" rx="9" ry="24" fill="#f9a825" />
                    <path d="M74 80 C66 92 64 112 70 130 C62 120 60 98 66 84 Z" fill="#7cb342" />
                    <path d="M74 80 C82 92 84 112 78 130 C86 118 88 98 82 84 Z" fill="#689f38" />
                    <path d="M70 92 h8 M69 100 h10 M69 108 h10 M70 116 h8" stroke="#e0941a" stroke-width="1.6" stroke-linecap="round" />
                </g>
                <path d="M60 30 L50 6 M60 30 L60 2 M60 30 L70 6 M60 30 L44 16 M60 30 L76 16" stroke="#c9a227" stroke-width="2.4" stroke-linecap="round" />
            </symbol>
        </defs>

        <rect width="640" height="420" fill="url(#ladang-langit)" />

        {{-- Matahari --}}
        <g class="matahari">
            <circle class="sinar" cx="452" cy="150" r="86" fill="#f9a825" opacity="0.22" />
            <circle cx="452" cy="150" r="52" fill="#f9a825" />
        </g>

        {{-- Awan --}}
        <g class="awan" fill="#ffffff" opacity="0.85">
            <path d="M92 104 a22 22 0 0 1 40 -12 a26 26 0 0 1 48 8 a18 18 0 0 1 4 34 h-88 a16 16 0 0 1 -4 -30 Z" />
            <path d="M520 70 a16 16 0 0 1 30 -8 a20 20 0 0 1 36 6 a13 13 0 0 1 2 24 h-66 a12 12 0 0 1 -2 -22 Z" opacity="0.8" />
        </g>

        {{-- Burung kecil --}}
        <g class="awan" stroke="#14491a" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.55" style="animation-duration: 26s">
            <path d="M250 96 q7 -7 14 0 q7 -7 14 0" />
            <path d="M286 76 q5 -5 10 0 q5 -5 10 0" />
        </g>

        {{-- Bukit jauh --}}
        <g class="lapisan" style="--urutan: 0">
            <path d="M0 250 C90 196 170 214 250 226 C340 240 420 186 520 196 C580 202 620 220 640 230 V420 H0 Z" fill="#b8cf96" />
        </g>

        {{-- Bukit tengah dengan petak ladang dan rumah --}}
        <g class="lapisan" style="--urutan: 1">
            <path d="M0 292 C110 250 220 262 320 270 C430 280 520 246 640 258 V420 H0 Z" fill="#86ad5c" />
            <g stroke="#739c4b" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.9">
                <path d="M40 300 C140 274 240 280 330 286" />
                <path d="M20 318 C130 292 250 298 360 304" />
                <path d="M380 288 C460 276 540 266 630 270" />
            </g>
            {{-- Rumah/balai kecil di lereng --}}
            <g transform="translate(118 252)">
                <rect x="0" y="12" width="40" height="24" fill="#fdf3dc" />
                <path d="M-6 14 L20 -6 L46 14 Z" fill="#d9731a" />
                <rect x="16" y="22" width="9" height="14" fill="#8a5a12" />
            </g>
            @foreach ($batangTengah as [$x, $y, $s, $lama, $tunda])
                <g transform="translate({{ $x }} {{ $y }}) scale({{ $s }})">
                    <g class="batang-jagung" style="--lama: {{ $lama }}; --tunda: {{ $tunda }}">
                        <use href="#ladang-jagung" x="-60" y="-200" width="120" height="200" />
                    </g>
                </g>
            @endforeach
        </g>

        {{-- Tanah depan --}}
        <g class="lapisan" style="--urutan: 2">
            <path d="M0 360 C120 330 260 340 340 346 C440 354 540 330 640 338 V420 H0 Z" fill="url(#ladang-tanah)" />
        </g>

        {{-- Barisan jagung depan --}}
        <g class="lapisan" style="--urutan: 3">
            @foreach ($batangDepan as [$x, $y, $s, $lama, $tunda])
                <g transform="translate({{ $x + 30 }} {{ $y + 140 }}) scale({{ $s }})">
                    <g class="batang-jagung" style="--lama: {{ $lama }}; --tunda: {{ $tunda }}">
                        <use href="#ladang-jagung" x="-60" y="-200" width="120" height="200" />
                    </g>
                </g>
            @endforeach
        </g>
    </svg>
</div>
