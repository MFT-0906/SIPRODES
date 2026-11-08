@php
    $identitas = app(\App\Support\KontenPublik::class)->identitas();
    // Kontak resmi hanya tampil setelah diverifikasi pemerintah desa.
    $kontak = collect($identitas['kontak'])->filter(fn ($k) => $k['verified'] && filled($k['value']));
    $tautan = \App\Support\Navigation::footer();
    $sekarang = now('Asia/Jakarta')->locale('id');
    $namaBesar = $identitas['nama'];
@endphp

{{--
    Footer: ladang jagung yang bergoyang tertiup angin (dua lapis, bergerak seperti gelombang),
    kalimat penutup, tautan, kantor desa, jam waktu setempat, lalu nama desa berukuran besar
    yang hurufnya muncul satu per satu saat footer terlihat.
--}}
<footer class="on-dark text-primary-soft">
    {{-- Pita ladang: latarnya mengikuti warna bagian terakhir halaman agar tidak ada garis sambungan (lihat app.css). --}}
    <div class="celah-ladang relative h-[104px] bg-surface md:h-[150px]">
    <svg viewBox="0 0 1440 124" preserveAspectRatio="xMidYMax slice" class="ladang-footer pointer-events-none absolute inset-x-0 bottom-0 h-[88px] w-full md:h-[124px]" aria-hidden="true" focusable="false">
        <defs>
            {{-- Jagung tinggi bertongkol dan bermalai --}}
            <symbol id="lad-a" viewBox="0 0 40 120" overflow="visible">
                <path d="M18.8 120 L19.5 14 L20.5 14 L21.2 120Z" />
                <path d="M20 94 Q8 86 1 66 Q12 80 20 88Z" />
                <path d="M20 76 Q32 68 39 50 Q28 63 20 70Z" />
                <path d="M20 54 Q10 46 5 30 Q14 43 20 49Z" />
                <path d="M20 38 Q28 30 33 18 Q26 27 20 33Z" />
                <ellipse cx="24.5" cy="70" rx="3.2" ry="8.5" transform="rotate(16 24.5 70)" />
                <path d="M20 15 L15 2 L19.2 13Z M20 15 L20.6 0 L21 14Z M20 15 L25 3 L21 14Z" />
            </symbol>
            {{-- Jagung sedang --}}
            <symbol id="lad-b" viewBox="0 0 40 120" overflow="visible">
                <path d="M19 120 L19.6 40 L20.4 40 L21 120Z" />
                <path d="M20 104 Q7 98 2 82 Q12 94 20 98Z" />
                <path d="M20 86 Q33 80 38 64 Q28 76 20 81Z" />
                <path d="M20 66 Q11 58 8 44 Q15 56 20 61Z" />
                <path d="M20 42 L17 30 L19.6 40Z M20 42 L23.5 31 L20.4 40Z" />
            </symbol>
            {{-- Tanaman muda berdaun --}}
            <symbol id="lad-c" viewBox="0 0 40 120" overflow="visible">
                <path d="M19.2 120 L19.7 70 L20.3 70 L20.8 120Z" />
                <path d="M20 112 Q4 106 0 88 Q11 102 20 106Z" />
                <path d="M20 106 Q36 100 40 82 Q29 97 20 101Z" />
                <path d="M20 92 Q10 84 9 68 Q15 82 20 87Z" />
                <path d="M20 86 Q30 78 31 64 Q25 77 20 82Z" />
            </symbol>
        </defs>

        <g fill="#24622a">
            <use href="#lad-c" x="-26.4" y="13.7" width="32.8" height="98.3" class="batang-ladang" style="--tunda:-0.31s;--lama:4.38s" />
            <use href="#lad-a" x="-2.7" y="32.5" width="26.5" height="79.5" class="batang-ladang" style="--tunda:-0.24s;--lama:4.23s" />
            <use href="#lad-a" x="20.0" y="25.3" width="28.9" height="86.7" class="batang-ladang" style="--tunda:-0.18s;--lama:4.14s" />
            <use href="#lad-a" x="44.2" y="21.2" width="30.3" height="90.8" class="batang-ladang" style="--tunda:-0.36s;--lama:4.05s" />
            <use href="#lad-a" x="67.5" y="20.4" width="30.5" height="91.6" class="batang-ladang" style="--tunda:-0.62s;--lama:3.66s" />
            <use href="#lad-a" x="83.9" y="21.1" width="30.3" height="90.9" class="batang-ladang" style="--tunda:-0.66s;--lama:3.93s" />
            <use href="#lad-a" x="106.2" y="23.3" width="29.6" height="88.7" class="batang-ladang" style="--tunda:-0.68s;--lama:4.10s" />
            <use href="#lad-c" x="128.2" y="19.5" width="30.8" height="92.5" class="batang-ladang" style="--tunda:-0.66s;--lama:4.15s" />
            <use href="#lad-a" x="153.3" y="18.1" width="31.3" height="93.9" class="batang-ladang" style="--tunda:-0.71s;--lama:3.83s" />
            <use href="#lad-a" x="172.8" y="22.1" width="30.0" height="89.9" class="batang-ladang" style="--tunda:-0.70s;--lama:3.71s" />
            <use href="#lad-a" x="189.9" y="11.2" width="33.6" height="100.8" class="batang-ladang" style="--tunda:-1.06s;--lama:3.60s" />
            <use href="#lad-a" x="210.3" y="24.6" width="29.1" height="87.4" class="batang-ladang" style="--tunda:-1.17s;--lama:4.00s" />
            <use href="#lad-a" x="225.6" y="16.1" width="32.0" height="95.9" class="batang-ladang" style="--tunda:-0.95s;--lama:3.69s" />
            <use href="#lad-c" x="245.0" y="16.7" width="31.8" height="95.3" class="batang-ladang" style="--tunda:-0.95s;--lama:3.85s" />
            <use href="#lad-a" x="263.4" y="24.8" width="29.1" height="87.2" class="batang-ladang" style="--tunda:-1.16s;--lama:4.28s" />
            <use href="#lad-a" x="278.9" y="10.4" width="33.9" height="101.6" class="batang-ladang" style="--tunda:-1.33s;--lama:4.02s" />
            <use href="#lad-c" x="301.3" y="26.0" width="28.7" height="86.0" class="batang-ladang" style="--tunda:-1.18s;--lama:3.87s" />
            <use href="#lad-b" x="324.4" y="10.1" width="34.0" height="101.9" class="batang-ladang" style="--tunda:-1.19s;--lama:3.79s" />
            <use href="#lad-a" x="354.7" y="36.4" width="25.2" height="75.6" class="batang-ladang" style="--tunda:-1.33s;--lama:4.04s" />
            <use href="#lad-b" x="367.2" y="14.7" width="32.4" height="97.3" class="batang-ladang" style="--tunda:-1.49s;--lama:3.67s" />
            <use href="#lad-a" x="389.0" y="37.2" width="24.9" height="74.8" class="batang-ladang" style="--tunda:-1.54s;--lama:4.22s" />
            <use href="#lad-c" x="402.5" y="14.6" width="32.5" height="97.4" class="batang-ladang" style="--tunda:-1.51s;--lama:3.99s" />
            <use href="#lad-b" x="424.4" y="12.5" width="33.2" height="99.5" class="batang-ladang" style="--tunda:-1.86s;--lama:3.85s" />
            <use href="#lad-a" x="443.4" y="18.6" width="31.1" height="93.4" class="batang-ladang" style="--tunda:-1.75s;--lama:4.08s" />
            <use href="#lad-a" x="462.8" y="34.7" width="25.8" height="77.3" class="batang-ladang" style="--tunda:-1.67s;--lama:4.56s" />
            <use href="#lad-c" x="480.5" y="30.5" width="27.2" height="81.5" class="batang-ladang" style="--tunda:-2.05s;--lama:4.20s" />
            <use href="#lad-a" x="496.4" y="12.0" width="33.3" height="100.0" class="batang-ladang" style="--tunda:-2.17s;--lama:3.73s" />
            <use href="#lad-a" x="520.1" y="29.9" width="27.4" height="82.1" class="batang-ladang" style="--tunda:-2.22s;--lama:3.80s" />
            <use href="#lad-b" x="535.7" y="26.2" width="28.6" height="85.8" class="batang-ladang" style="--tunda:-2.01s;--lama:3.65s" />
            <use href="#lad-a" x="556.0" y="35.1" width="25.6" height="76.9" class="batang-ladang" style="--tunda:-2.03s;--lama:4.05s" />
            <use href="#lad-a" x="573.0" y="21.3" width="30.2" height="90.7" class="batang-ladang" style="--tunda:-2.41s;--lama:4.07s" />
            <use href="#lad-b" x="590.9" y="11.0" width="33.7" height="101.0" class="batang-ladang" style="--tunda:-2.12s;--lama:4.24s" />
            <use href="#lad-b" x="614.6" y="28.8" width="27.7" height="83.2" class="batang-ladang" style="--tunda:-2.58s;--lama:3.68s" />
            <use href="#lad-a" x="633.4" y="12.8" width="33.1" height="99.2" class="batang-ladang" style="--tunda:-2.55s;--lama:4.30s" />
            <use href="#lad-b" x="659.9" y="27.9" width="28.0" height="84.1" class="batang-ladang" style="--tunda:-2.61s;--lama:4.50s" />
            <use href="#lad-c" x="681.9" y="11.5" width="33.5" height="100.5" class="batang-ladang" style="--tunda:-2.44s;--lama:4.10s" />
            <use href="#lad-c" x="700.6" y="27.1" width="28.3" height="84.9" class="batang-ladang" style="--tunda:-2.49s;--lama:3.67s" />
            <use href="#lad-a" x="714.7" y="10.2" width="33.9" height="101.8" class="batang-ladang" style="--tunda:-2.89s;--lama:4.33s" />
            <use href="#lad-c" x="737.1" y="25.4" width="28.9" height="86.6" class="batang-ladang" style="--tunda:-2.94s;--lama:3.68s" />
            <use href="#lad-a" x="761.2" y="29.0" width="27.7" height="83.0" class="batang-ladang" style="--tunda:-2.73s;--lama:3.62s" />
            <use href="#lad-a" x="785.9" y="23.9" width="29.4" height="88.1" class="batang-ladang" style="--tunda:-3.03s;--lama:4.52s" />
            <use href="#lad-a" x="805.1" y="27.4" width="28.2" height="84.6" class="batang-ladang" style="--tunda:-2.90s;--lama:4.21s" />
            <use href="#lad-b" x="824.9" y="19.4" width="30.9" height="92.6" class="batang-ladang" style="--tunda:-3.09s;--lama:4.49s" />
            <use href="#lad-b" x="846.3" y="32.1" width="26.6" height="79.9" class="batang-ladang" style="--tunda:-3.16s;--lama:4.41s" />
            <use href="#lad-a" x="870.6" y="27.0" width="28.3" height="85.0" class="batang-ladang" style="--tunda:-3.31s;--lama:3.92s" />
            <use href="#lad-c" x="888.1" y="27.9" width="28.0" height="84.1" class="batang-ladang" style="--tunda:-3.49s;--lama:3.64s" />
            <use href="#lad-b" x="902.6" y="14.9" width="32.4" height="97.1" class="batang-ladang" style="--tunda:-3.24s;--lama:4.07s" />
            <use href="#lad-c" x="929.9" y="27.0" width="28.3" height="85.0" class="batang-ladang" style="--tunda:-3.49s;--lama:4.27s" />
            <use href="#lad-c" x="953.3" y="28.8" width="27.7" height="83.2" class="batang-ladang" style="--tunda:-3.69s;--lama:3.88s" />
            <use href="#lad-b" x="974.3" y="21.9" width="30.0" height="90.1" class="batang-ladang" style="--tunda:-3.56s;--lama:4.39s" />
            <use href="#lad-a" x="991.2" y="26.4" width="28.5" height="85.6" class="batang-ladang" style="--tunda:-3.57s;--lama:4.37s" />
            <use href="#lad-a" x="1008.1" y="15.6" width="32.1" height="96.4" class="batang-ladang" style="--tunda:-3.95s;--lama:3.72s" />
            <use href="#lad-b" x="1024.4" y="11.1" width="33.6" height="100.9" class="batang-ladang" style="--tunda:-3.89s;--lama:3.80s" />
            <use href="#lad-a" x="1046.3" y="17.9" width="31.4" height="94.1" class="batang-ladang" style="--tunda:-3.99s;--lama:4.13s" />
            <use href="#lad-a" x="1064.7" y="30.1" width="27.3" height="81.9" class="batang-ladang" style="--tunda:-3.88s;--lama:4.30s" />
            <use href="#lad-a" x="1085.3" y="26.7" width="28.4" height="85.3" class="batang-ladang" style="--tunda:-4.13s;--lama:4.51s" />
            <use href="#lad-c" x="1103.4" y="34.0" width="26.0" height="78.0" class="batang-ladang" style="--tunda:-4.06s;--lama:4.23s" />
            <use href="#lad-c" x="1126.6" y="22.4" width="29.9" height="89.6" class="batang-ladang" style="--tunda:-4.22s;--lama:4.10s" />
            <use href="#lad-b" x="1151.2" y="24.8" width="29.1" height="87.2" class="batang-ladang" style="--tunda:-4.31s;--lama:3.80s" />
            <use href="#lad-a" x="1175.6" y="31.7" width="26.8" height="80.3" class="batang-ladang" style="--tunda:-4.49s;--lama:4.58s" />
            <use href="#lad-b" x="1201.1" y="30.4" width="27.2" height="81.6" class="batang-ladang" style="--tunda:-4.51s;--lama:3.62s" />
            <use href="#lad-a" x="1221.3" y="25.4" width="28.9" height="86.6" class="batang-ladang" style="--tunda:-4.51s;--lama:4.37s" />
            <use href="#lad-a" x="1243.2" y="31.6" width="26.8" height="80.4" class="batang-ladang" style="--tunda:-4.71s;--lama:4.03s" />
            <use href="#lad-b" x="1257.4" y="18.7" width="31.1" height="93.3" class="batang-ladang" style="--tunda:-4.79s;--lama:4.07s" />
            <use href="#lad-c" x="1282.2" y="11.5" width="33.5" height="100.5" class="batang-ladang" style="--tunda:-4.71s;--lama:4.55s" />
            <use href="#lad-a" x="1301.4" y="25.5" width="28.8" height="86.5" class="batang-ladang" style="--tunda:-4.79s;--lama:4.43s" />
            <use href="#lad-a" x="1322.6" y="23.2" width="29.6" height="88.8" class="batang-ladang" style="--tunda:-4.98s;--lama:4.16s" />
            <use href="#lad-c" x="1339.8" y="11.2" width="33.6" height="100.8" class="batang-ladang" style="--tunda:-5.06s;--lama:4.21s" />
            <use href="#lad-a" x="1358.8" y="10.9" width="33.7" height="101.1" class="batang-ladang" style="--tunda:-4.99s;--lama:4.17s" />
            <use href="#lad-a" x="1378.9" y="23.7" width="29.4" height="88.3" class="batang-ladang" style="--tunda:-5.08s;--lama:3.63s" />
            <use href="#lad-c" x="1404.4" y="22.6" width="29.8" height="89.4" class="batang-ladang" style="--tunda:-5.32s;--lama:3.72s" />
            <use href="#lad-a" x="1423.5" y="35.8" width="25.4" height="76.2" class="batang-ladang" style="--tunda:-5.20s;--lama:4.01s" />
        </g>
        <g fill="#14491a">
            <use href="#lad-c" x="-27.2" y="20.7" width="34.4" height="103.3" class="batang-ladang" style="--tunda:-0.15s;--lama:3.73s" />
            <use href="#lad-c" x="-5.5" y="11.5" width="37.5" height="112.5" class="batang-ladang" style="--tunda:-0.09s;--lama:4.02s" />
            <use href="#lad-b" x="15.2" y="25.7" width="32.8" height="98.3" class="batang-ladang" style="--tunda:-0.42s;--lama:3.62s" />
            <use href="#lad-a" x="36.2" y="30.0" width="31.3" height="94.0" class="batang-ladang" style="--tunda:-0.29s;--lama:4.32s" />
            <use href="#lad-c" x="56.6" y="26.6" width="32.5" height="97.4" class="batang-ladang" style="--tunda:-0.55s;--lama:3.72s" />
            <use href="#lad-b" x="77.0" y="4.6" width="39.8" height="119.4" class="batang-ladang" style="--tunda:-0.62s;--lama:4.48s" />
            <use href="#lad-c" x="94.7" y="0.7" width="41.1" height="123.3" class="batang-ladang" style="--tunda:-0.65s;--lama:4.14s" />
            <use href="#lad-a" x="124.8" y="11.3" width="37.6" height="112.7" class="batang-ladang" style="--tunda:-0.84s;--lama:4.21s" />
            <use href="#lad-a" x="150.8" y="-2.5" width="42.2" height="126.5" class="batang-ladang" style="--tunda:-0.85s;--lama:3.95s" />
            <use href="#lad-b" x="171.1" y="-1.7" width="41.9" height="125.7" class="batang-ladang" style="--tunda:-0.74s;--lama:4.38s" />
            <use href="#lad-a" x="196.0" y="25.6" width="32.8" height="98.4" class="batang-ladang" style="--tunda:-0.83s;--lama:4.33s" />
            <use href="#lad-a" x="215.3" y="14.7" width="36.4" height="109.3" class="batang-ladang" style="--tunda:-1.19s;--lama:4.14s" />
            <use href="#lad-a" x="241.9" y="15.9" width="36.0" height="108.1" class="batang-ladang" style="--tunda:-1.18s;--lama:3.62s" />
            <use href="#lad-b" x="259.3" y="-2.4" width="42.1" height="126.4" class="batang-ladang" style="--tunda:-1.36s;--lama:3.72s" />
            <use href="#lad-b" x="285.8" y="12.3" width="37.2" height="111.7" class="batang-ladang" style="--tunda:-1.33s;--lama:3.79s" />
            <use href="#lad-a" x="307.5" y="29.5" width="31.5" height="94.5" class="batang-ladang" style="--tunda:-1.43s;--lama:4.21s" />
            <use href="#lad-c" x="335.7" y="25.1" width="33.0" height="98.9" class="batang-ladang" style="--tunda:-1.30s;--lama:3.80s" />
            <use href="#lad-b" x="359.1" y="-3.0" width="42.3" height="127.0" class="batang-ladang" style="--tunda:-1.36s;--lama:3.66s" />
            <use href="#lad-c" x="393.6" y="27.6" width="32.1" height="96.4" class="batang-ladang" style="--tunda:-1.46s;--lama:3.99s" />
            <use href="#lad-b" x="414.6" y="14.9" width="36.4" height="109.1" class="batang-ladang" style="--tunda:-1.74s;--lama:3.61s" />
            <use href="#lad-a" x="442.5" y="23.9" width="33.4" height="100.1" class="batang-ladang" style="--tunda:-1.78s;--lama:4.34s" />
            <use href="#lad-a" x="462.8" y="8.6" width="38.5" height="115.4" class="batang-ladang" style="--tunda:-1.71s;--lama:4.39s" />
            <use href="#lad-a" x="483.5" y="1.5" width="40.8" height="122.5" class="batang-ladang" style="--tunda:-2.03s;--lama:4.46s" />
            <use href="#lad-c" x="510.5" y="9.9" width="38.0" height="114.1" class="batang-ladang" style="--tunda:-1.91s;--lama:4.18s" />
            <use href="#lad-b" x="534.3" y="20.3" width="34.6" height="103.7" class="batang-ladang" style="--tunda:-1.96s;--lama:4.34s" />
            <use href="#lad-c" x="555.1" y="25.6" width="32.8" height="98.4" class="batang-ladang" style="--tunda:-2.12s;--lama:4.14s" />
            <use href="#lad-c" x="573.7" y="2.9" width="40.4" height="121.1" class="batang-ladang" style="--tunda:-2.22s;--lama:4.32s" />
            <use href="#lad-b" x="595.4" y="19.5" width="34.8" height="104.5" class="batang-ladang" style="--tunda:-2.17s;--lama:4.49s" />
            <use href="#lad-a" x="625.7" y="22.0" width="34.0" height="102.0" class="batang-ladang" style="--tunda:-2.61s;--lama:4.27s" />
            <use href="#lad-c" x="645.7" y="9.9" width="38.0" height="114.1" class="batang-ladang" style="--tunda:-2.52s;--lama:3.99s" />
            <use href="#lad-a" x="674.9" y="5.2" width="39.6" height="118.8" class="batang-ladang" style="--tunda:-2.72s;--lama:4.58s" />
            <use href="#lad-a" x="692.9" y="3.8" width="40.1" height="120.2" class="batang-ladang" style="--tunda:-2.58s;--lama:4.00s" />
            <use href="#lad-a" x="712.1" y="7.1" width="39.0" height="116.9" class="batang-ladang" style="--tunda:-2.54s;--lama:3.86s" />
            <use href="#lad-b" x="734.0" y="10.6" width="37.8" height="113.4" class="batang-ladang" style="--tunda:-2.82s;--lama:4.57s" />
            <use href="#lad-a" x="758.3" y="7.4" width="38.9" height="116.6" class="batang-ladang" style="--tunda:-3.02s;--lama:3.68s" />
            <use href="#lad-a" x="785.0" y="16.5" width="35.8" height="107.5" class="batang-ladang" style="--tunda:-3.16s;--lama:3.99s" />
            <use href="#lad-c" x="813.7" y="10.9" width="37.7" height="113.1" class="batang-ladang" style="--tunda:-3.13s;--lama:4.03s" />
            <use href="#lad-b" x="837.7" y="11.4" width="37.5" height="112.6" class="batang-ladang" style="--tunda:-3.21s;--lama:3.97s" />
            <use href="#lad-b" x="858.9" y="10.2" width="37.9" height="113.8" class="batang-ladang" style="--tunda:-3.16s;--lama:4.32s" />
            <use href="#lad-a" x="879.2" y="2.6" width="40.5" height="121.4" class="batang-ladang" style="--tunda:-3.36s;--lama:4.23s" />
            <use href="#lad-c" x="906.4" y="28.5" width="31.8" height="95.5" class="batang-ladang" style="--tunda:-3.33s;--lama:4.00s" />
            <use href="#lad-b" x="931.3" y="27.8" width="32.1" height="96.2" class="batang-ladang" style="--tunda:-3.65s;--lama:4.03s" />
            <use href="#lad-a" x="955.2" y="28.8" width="31.7" height="95.2" class="batang-ladang" style="--tunda:-3.41s;--lama:4.41s" />
            <use href="#lad-a" x="978.5" y="-4.8" width="42.9" height="128.8" class="batang-ladang" style="--tunda:-3.80s;--lama:4.20s" />
            <use href="#lad-c" x="1001.0" y="14.0" width="36.7" height="110.0" class="batang-ladang" style="--tunda:-3.62s;--lama:3.65s" />
            <use href="#lad-a" x="1025.9" y="17.3" width="35.6" height="106.7" class="batang-ladang" style="--tunda:-3.68s;--lama:4.02s" />
            <use href="#lad-c" x="1051.1" y="17.3" width="35.6" height="106.7" class="batang-ladang" style="--tunda:-4.01s;--lama:4.42s" />
            <use href="#lad-b" x="1068.5" y="4.0" width="40.0" height="120.0" class="batang-ladang" style="--tunda:-3.88s;--lama:3.71s" />
            <use href="#lad-b" x="1100.0" y="20.5" width="34.5" height="103.5" class="batang-ladang" style="--tunda:-4.12s;--lama:4.33s" />
            <use href="#lad-a" x="1124.2" y="7.6" width="38.8" height="116.4" class="batang-ladang" style="--tunda:-4.27s;--lama:3.79s" />
            <use href="#lad-a" x="1143.8" y="-1.2" width="41.7" height="125.2" class="batang-ladang" style="--tunda:-4.06s;--lama:3.66s" />
            <use href="#lad-c" x="1170.1" y="29.4" width="31.5" height="94.6" class="batang-ladang" style="--tunda:-4.13s;--lama:3.82s" />
            <use href="#lad-a" x="1186.1" y="-2.5" width="42.2" height="126.5" class="batang-ladang" style="--tunda:-4.26s;--lama:3.83s" />
            <use href="#lad-c" x="1218.5" y="13.2" width="36.9" height="110.8" class="batang-ladang" style="--tunda:-4.38s;--lama:3.94s" />
            <use href="#lad-a" x="1243.5" y="1.5" width="40.8" height="122.5" class="batang-ladang" style="--tunda:-4.57s;--lama:4.42s" />
            <use href="#lad-a" x="1273.7" y="14.4" width="36.5" height="109.6" class="batang-ladang" style="--tunda:-4.79s;--lama:3.99s" />
            <use href="#lad-a" x="1292.3" y="6.9" width="39.0" height="117.1" class="batang-ladang" style="--tunda:-4.95s;--lama:3.93s" />
            <use href="#lad-a" x="1318.2" y="15.0" width="36.3" height="109.0" class="batang-ladang" style="--tunda:-4.79s;--lama:3.70s" />
            <use href="#lad-a" x="1346.0" y="10.8" width="37.7" height="113.2" class="batang-ladang" style="--tunda:-4.91s;--lama:4.17s" />
            <use href="#lad-c" x="1375.3" y="19.6" width="34.8" height="104.4" class="batang-ladang" style="--tunda:-5.15s;--lama:3.68s" />
            <use href="#lad-a" x="1393.5" y="5.4" width="39.5" height="118.6" class="batang-ladang" style="--tunda:-5.10s;--lama:4.36s" />
            <use href="#lad-a" x="1418.3" y="22.5" width="33.8" height="101.5" class="batang-ladang" style="--tunda:-5.25s;--lama:3.93s" />
        </g>
    </svg>
    </div>

    <div class="bg-primary-dark">
    <div class="container-page pt-6 md:pt-10">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-20">
            {{-- Kalimat penutup --}}
            <div class="flex flex-col gap-5">
                <p class="max-w-[22ch] font-display text-[28px] font-bold leading-[1.2] text-surface md:text-[36px]">
                    Tumbuh dari ladang jagung, dibuka warga bedol desa asal Kaligesik.
                </p>
                <p class="max-w-[46ch] type-body-sm text-primary-soft/80">{{ $identitas['sebutan'] }} {{ $identitas['nama'] }}, {{ $identitas['wilayah'] }}</p>
                <p
                    x-data="jamDesa(@js($sekarang->format('H.i')))"
                    class="inline-flex w-fit items-center gap-2.5 rounded-pill border border-surface/15 px-3.5 py-1.5 type-label-sm text-surface/90"
                >
                    <span class="relative flex size-2">
                        <span class="titik-denyut absolute inset-0 rounded-full bg-accent"></span>
                        <span class="relative size-2 rounded-full bg-accent"></span>
                    </span>
                    <span>Di Marga Agung sekarang pukul <span class="tabular-nums" x-text="jam">{{ $sekarang->format('H.i') }}</span> WIB</span>
                </p>
            </div>

            <div class="grid gap-10 sm:grid-cols-2">
                <nav aria-label="Tautan penting" class="flex flex-col gap-3">
                    <h2 class="type-label-sm text-accent">Tautan Penting</h2>
                    <ul class="flex flex-col">
                        @foreach ($tautan as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" class="garis-tautan inline-flex min-h-11 items-center type-body text-surface/90 hover:text-surface md:min-h-0 md:py-1.5">{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="flex flex-col gap-3">
                    @if ($kontak->isNotEmpty())
                        <h2 class="type-label-sm text-accent">Kontak Resmi</h2>
                        <ul class="flex flex-col gap-3 type-body-sm">
                            @isset($kontak['alamat'])
                                <li>{{ $kontak['alamat']['value'] }}</li>
                            @endisset
                            @isset($kontak['telepon'])
                                <li><a href="tel:{{ preg_replace('/[^\d+]/', '', $kontak['telepon']['value']) }}" class="garis-tautan text-surface">{{ $kontak['telepon']['value'] }}</a></li>
                            @endisset
                            @isset($kontak['email'])
                                <li><a href="mailto:{{ $kontak['email']['value'] }}" class="garis-tautan break-all text-surface">{{ $kontak['email']['value'] }}</a></li>
                            @endisset
                            @isset($kontak['jam_layanan'])
                                <li>Layanan: {{ $kontak['jam_layanan']['value'] }}</li>
                            @endisset
                        </ul>
                    @else
                        <h2 class="type-label-sm text-accent">Kantor Desa</h2>
                        <p class="type-body-sm">Alamat, telepon, dan jam layanan kantor akan ditampilkan setelah diverifikasi pemerintah desa.</p>
                    @endif
                    <a href="{{ route('aspirasi.create') }}" class="garis-tautan mt-2 w-fit type-label text-accent">Kirim pertanyaan lewat Ruang Aspirasi</a>
                </div>
            </div>
        </div>

        {{-- Nama desa berukuran besar; huruf naik satu per satu saat terlihat. --}}
        <div x-data x-muncul class="nama-besar mt-14 select-none overflow-hidden md:mt-20" aria-hidden="true">
            <p class="flex justify-between whitespace-nowrap font-display font-bold leading-[0.78] tracking-[-0.02em] text-primary">
                @foreach (mb_str_split($namaBesar) as $huruf)
                    <span class="huruf" style="--urutan: {{ $loop->index }}">{!! $huruf === ' ' ? '&nbsp;' : e($huruf) !!}</span>
                @endforeach
            </p>
        </div>

        <div class="flex flex-col gap-3 border-t border-surface/10 py-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="type-label-sm">© {{ date('Y') }} {{ $identitas['sebutan'] }} {{ $identitas['nama'] }}</p>
            <a href="#" class="garis-tautan w-fit type-label-sm text-surface">Kembali ke atas</a>
        </div>
    </div>
    </div>
</footer>
