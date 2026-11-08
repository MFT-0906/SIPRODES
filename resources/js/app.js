import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);

const formatSize = (bytes) => {
    if (bytes >= 1024 * 1024) {
        return (bytes / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
    }

    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
};

/**
 * Pengunggah berkas. Pemeriksaan di sini hanya untuk kenyamanan pengguna;
 * validasi tipe, isi, dan ukuran tetap wajib di server.
 */
Alpine.data('uploader', (config = {}) => ({
    status: config.status ?? 'kosong', // kosong | mengunggah | berhasil | ditolak
    name: config.name ?? '',
    size: config.size ?? '',
    progress: config.progress ?? 0,
    message: config.message ?? '',
    accept: config.accept ?? [],
    maxBytes: config.maxMb ? config.maxMb * 1024 * 1024 : null,
    acceptLabel: config.acceptLabel ?? '',

    choose(event) {
        const file = event.target.files?.[0];

        if (file) {
            this.check(file);
        }
    },

    drop(event) {
        const file = event.dataTransfer?.files?.[0];

        if (!file) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.input.files = transfer.files;
        this.check(file);
    },

    check(file) {
        this.name = file.name;
        this.size = formatSize(file.size);

        if (this.accept.length && !this.accept.includes(file.type)) {
            this.status = 'ditolak';
            this.message = `Ditolak: hanya ${this.acceptLabel} yang diizinkan. Pilih berkas lain.`;
            this.$refs.input.value = '';

            return;
        }

        if (this.maxBytes && file.size > this.maxBytes) {
            this.status = 'ditolak';
            this.message = `Ditolak: ukuran berkas melebihi ${config.maxMb} MB. Pilih berkas lain.`;
            this.$refs.input.value = '';

            return;
        }

        this.status = 'berhasil';
        this.message = '';
    },

    clear() {
        this.$refs.input.value = '';
        this.status = 'kosong';
        this.name = '';
        this.size = '';
        this.message = '';
        this.$refs.input.focus();
    },
}));

/**
 * Wilayah notifikasi singkat. Panggil dengan:
 * window.dispatchEvent(new CustomEvent('toast', { detail: { message: '...', tone: 'sukses' } }))
 */
Alpine.data('toastRegion', (initialMessage = null, initialTone = 'sukses') => ({
    items: [],
    counter: 0,

    init() {
        if (initialMessage) {
            this.show({ message: initialMessage, tone: initialTone });
        }
    },

    show({ message, tone = 'sukses' }) {
        const id = ++this.counter;
        this.items.push({ id, message, tone });
        setTimeout(() => this.dismiss(id), 6000);
    },

    dismiss(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
}));

/**
 * Editor isi sederhana: tombol format menyisipkan penanda teks ringan pada textarea.
 * Hanya kenyamanan penulis; isi tetap harus dibersihkan di server.
 */
Alpine.data('editorIsi', () => ({
    terapkan(jenis, awal, akhir, contoh) {
        const area = this.$refs.isi;
        const mulai = area.selectionStart;
        const selesai = area.selectionEnd;
        const terpilih = area.value.slice(mulai, selesai) || contoh;
        let sisip;

        if (jenis === 'awalan') {
            // Penanda di awal baris: mulai di baris baru bila kursor tidak berada di awal baris.
            const awalBaris = mulai === 0 || area.value[mulai - 1] === '\n';
            sisip = (awalBaris ? '' : '\n') + awal + terpilih;
        } else {
            sisip = awal + terpilih + akhir;
        }

        area.setRangeText(sisip, mulai, selesai, 'select');
        area.focus();
        area.dispatchEvent(new Event('input', { bubbles: true }));
    },
}));

/**
 * Daftar butir yang bisa ditambah, dihapus, dan diurutkan dengan tombol (mis. misi desa).
 * Urutan dikirim sebagai larik field berurutan; validasi tetap di server.
 */
Alpine.data('daftarButir', (awal = []) => ({
    butir: [],
    nomor: 0,

    init() {
        this.butir = awal.map((teks) => ({ id: ++this.nomor, teks }));

        if (!this.butir.length) {
            this.tambah(false);
        }
    },

    tambah(fokus = true) {
        this.butir.push({ id: ++this.nomor, teks: '' });

        if (fokus) {
            this.$nextTick(() => this.$refs.daftar.querySelector('li:last-child input')?.focus());
        }
    },

    hapus(i) {
        this.butir.splice(i, 1);
    },

    geser(i, arah) {
        const j = i + arah;

        if (j < 0 || j >= this.butir.length) {
            return;
        }

        [this.butir[i], this.butir[j]] = [this.butir[j], this.butir[i]];
    },
}));

/**
 * Navigasi bagian halaman: menandai bagian yang sedang dibaca (scrollspy) lewat aria-current.
 */
Alpine.data('sectionNav', (ids = []) => ({
    active: ids[0] ?? null,

    init() {
        // Bagian aktif = bagian terakhir yang judulnya sudah melewati garis di bawah header + navigasi menempel.
        // Di dasar halaman, bagian terakhir selalu aktif walau pendek.
        const perbarui = () => {
            const garis = this.$el.getBoundingClientRect().bottom + 40;
            const diDasar = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
            let aktif = ids[0] ?? null;

            ids.forEach((id) => {
                const bagian = document.getElementById(id);

                if (bagian && bagian.getBoundingClientRect().top <= garis) {
                    aktif = id;
                }
            });

            this.active = diDasar ? ids[ids.length - 1] : aktif;
        };

        let antre = false;
        window.addEventListener('scroll', () => {
            if (!antre) {
                antre = true;
                requestAnimationFrame(() => { antre = false; perbarui(); });
            }
        }, { passive: true });

        perbarui();
    },
}));

/**
 * Katalog Potensi Desa: tab Pertanian/UMKM (pola tablist ARIA, dapat dioperasikan dengan panah/Home/End)
 * Pencarian dilakukan di server (parameter ?q=) lewat formulir GET; tab aktif ikut dikirim.
 */
Alpine.data('katalog', (awal = 'pertanian', daftarTab = ['pertanian', 'umkm']) => ({
    tab: awal,

    init() {
        const dariHash = window.location.hash.replace('#', '');

        if (daftarTab.includes(dariHash)) {
            this.tab = dariHash;
        }
    },

    pilih(nama, fokus = false) {
        this.tab = nama;
        history.replaceState(null, '', '#' + nama);

        if (fokus) {
            this.$nextTick(() => document.getElementById('tab-' + nama)?.focus());
        }
    },

    tombol(event) {
        const i = daftarTab.indexOf(this.tab);
        const peta = {
            ArrowRight: (i + 1) % daftarTab.length,
            ArrowLeft: (i - 1 + daftarTab.length) % daftarTab.length,
            Home: 0,
            End: daftarTab.length - 1,
        };

        if (event.key in peta) {
            event.preventDefault();
            this.pilih(daftarTab[peta[event.key]], true);
        }
    },

}));

/**
 * Formulir Ruang Aspirasi: validasi di sisi klien hanya untuk kenyamanan (aturan sama dengan server).
 * Server tetap memvalidasi ulang di AspirasiController.
 */
Alpine.data('formAspirasi', () => ({
    err: {},
    ringkasan: '',

    periksa(event) {
        const form = event.target;
        const nilai = (nama) => (form.elements[nama]?.value ?? '').trim();
        const err = {};

        if (nilai('nama') === '') {
            err.nama = 'Nama wajib diisi.';
        } else if (nilai('nama').length > 100) {
            err.nama = 'Nama paling banyak 100 karakter.';
        }

        if (nilai('jawaban') === '') {
            err.jawaban = 'Jawab pertanyaan hitungan terlebih dahulu.';
        } else if (!/^\d{1,2}$/.test(nilai('jawaban'))) {
            err.jawaban = 'Tulis jawaban hitungan dengan angka.';
        }

        if (nilai('pesan') === '') {
            err.pesan = 'Isi pesan wajib diisi.';
        } else if (nilai('pesan').length < 20) {
            err.pesan = `Pesan terlalu pendek. Tulis minimal 20 karakter (saat ini ${nilai('pesan').length}).`;
        }

        this.err = err;
        const jumlah = Object.keys(err).length;
        this.ringkasan = jumlah ? `Ada ${jumlah} isian yang perlu diperbaiki sebelum pesan dapat dikirim.` : '';

        if (jumlah) {
            event.preventDefault();
            form.elements[Object.keys(err)[0]].focus();
        }
    },
}));

/**
 * Tombol "Bagikan tautan": memakai lembar bagikan perangkat bila ada, jika tidak menyalin tautan ke papan klip.
 */
Alpine.data('bagikan', (judul = '') => ({
    async kirim() {
        const tautan = window.location.href;

        if (navigator.share) {
            try {
                await navigator.share({ title: judul, url: tautan });

                return;
            } catch (galat) {
                if (galat.name === 'AbortError') {
                    return;
                }
            }
        }

        try {
            await navigator.clipboard.writeText(tautan);
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Tautan disalin ke papan klip.', tone: 'sukses' } }));
        } catch {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Tautan tidak dapat disalin. Salin dari bilah alamat peramban.', tone: 'bahaya' } }));
        }
    },
}));

// Cegah klik ganda: tombol kirim yang bertanda data-submit-loading dikunci saat formulir dikirim.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    form.querySelectorAll('[data-submit-loading]').forEach((button) => {
        button.setAttribute('aria-busy', 'true');
        setTimeout(() => {
            button.disabled = true;
        }, 0);
    });
});

// Saat halaman dipulihkan dari cache peramban (tombol Kembali), buka kunci tombol.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('[data-submit-loading]').forEach((button) => {
        button.removeAttribute('aria-busy');
        button.disabled = false;
    });
});

/**
 * Editor rincian APBDes: baris uraian + anggaran per jenis (pendapatan, belanja, pembiayaan).
 * Total ditampilkan otomatis dan hanya untuk bantuan; server menghitung ulang total dari rincian.
 */
Alpine.data('rincianApbdes', (awal = {}) => ({
    daftar: {
        pendapatan: (awal.pendapatan ?? []).map((b, i) => ({ ...b, id: `pendapatan-${i}` })),
        belanja: (awal.belanja ?? []).map((b, i) => ({ ...b, id: `belanja-${i}` })),
        pembiayaan: (awal.pembiayaan ?? []).map((b, i) => ({ ...b, id: `pembiayaan-${i}` })),
    },
    nomor: 1000,

    tambah(jenis) {
        this.daftar[jenis].push({ uraian: '', anggaran: '', id: `${jenis}-${this.nomor++}` });
        this.$nextTick(() => {
            const kolom = this.$root.querySelectorAll(`[data-jenis="${jenis}"] input[name$="[uraian]"]`);
            kolom[kolom.length - 1]?.focus();
        });
    },

    hapus(jenis, i) {
        this.daftar[jenis].splice(i, 1);
    },

    total(jenis) {
        const jumlah = this.daftar[jenis].reduce((sum, b) => {
            const angka = parseInt(String(b.anggaran ?? '').replace(/\./g, ''), 10);

            return sum + (Number.isNaN(angka) ? 0 : angka);
        }, 0);

        return 'Rp ' + jumlah.toLocaleString('id-ID');
    },
}));

/**
 * Peta lokasi desa (Leaflet): citra satelit Esri World Imagery dengan label jalan dan tempat (Esri Reference).
 * Leaflet dimuat hanya saat komponen ada di halaman (dynamic import), jadi halaman lain tidak terbebani.
 * Atribusi kedua sumber ditampilkan di pojok peta sesuai syarat lisensi.
 */
Alpine.data('petaDesa', (config = {}) => ({
    gagal: false,

    async init() {
        try {
            const [{ default: L }, ikon, ikon2x, bayangan] = await Promise.all([
                import('leaflet'),
                import('leaflet/dist/images/marker-icon.png'),
                import('leaflet/dist/images/marker-icon-2x.png'),
                import('leaflet/dist/images/marker-shadow.png'),
                import('leaflet/dist/leaflet.css'),
            ]);

            // Leaflet menebak lokasi gambar penanda dari nama berkas CSS; dengan bundler tebakan itu salah dan penanda
            // tampil sebagai gambar rusak. Hapus penebak lalu tunjuk langsung ke gambar yang sudah dibundel Vite.
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({ iconUrl: ikon.default, iconRetinaUrl: ikon2x.default, shadowUrl: bayangan.default });

            const peta = L.map(this.$refs.peta, {
                center: [config.lat, config.lng],
                zoom: config.zoom ?? 17,
                scrollWheelZoom: false, // roda mouse tetap menggulir halaman; aktif setelah peta diklik
                zoomControl: false,
            });

            L.control.zoom({ zoomInTitle: 'Perbesar', zoomOutTitle: 'Perkecil' }).addTo(peta);
            peta.attributionControl.setPrefix('<a href="https://leafletjs.com" target="_blank" rel="noopener noreferrer">Leaflet</a>');

            const satelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Citra &copy; <a href="https://www.esri.com" target="_blank" rel="noopener noreferrer">Esri</a>, Maxar, Earthstar Geographics, dan komunitas pengguna GIS',
            });

            // Label jalan dan nama tempat (Esri Reference) ditumpuk di atas citra satelit, seperti mode "hybrid".
            const labelJalan = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
            const labelTempat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
            const satelitBerlabel = L.layerGroup([satelit, labelJalan, labelTempat]);

            // Hanya satu tampilan: satelit berlabel (tanpa pilihan peta jalan).
            satelitBerlabel.addTo(peta);
            L.marker([config.lat, config.lng], { title: config.judul, alt: config.judul, keyboard: true }).addTo(peta).bindPopup(config.judul);

            peta.once('focus', () => peta.scrollWheelZoom.enable());
            peta.on('blur', () => peta.scrollWheelZoom.disable());
        } catch (e) {
            this.gagal = true;
        }
    },
}));

/*
 * Gerak saat digulir. Tanpa dependensi tambahan: IntersectionObserver bawaan peramban.
 * Pengguna yang memilih "kurangi gerak" langsung melihat keadaan akhir tanpa animasi.
 */
const kurangiGerak = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const saatTerlihat = (el, callback, ambang = 0.2) => {
    if (kurangiGerak() || !('IntersectionObserver' in window)) {
        callback();

        return;
    }

    const pengamat = new IntersectionObserver((entri) => {
        if (entri.some((e) => e.isIntersecting)) {
            pengamat.disconnect();
            callback();
        }
    }, { threshold: ambang, rootMargin: '0px 0px -40px 0px' });

    pengamat.observe(el);
};

// x-muncul: elemen muncul halus saat masuk layar. Bagian dalamnya bisa ikut beranimasi lewat [data-muncul="tampil"].
Alpine.directive('muncul', (el) => {
    if (kurangiGerak()) {
        el.dataset.muncul = 'tampil';

        return;
    }

    el.dataset.muncul = 'siap';
    saatTerlihat(el, () => requestAnimationFrame(() => { el.dataset.muncul = 'tampil'; }));
});

/**
 * Angka yang menghitung naik saat terlihat, mis. "± 4.492 jiwa". Awalan dan satuan dipertahankan;
 * teks asli tetap ada di HTML sehingga pembaca layar dan mesin pencari membaca angka sebenarnya.
 */
Alpine.data('hitungNaik', (teks = '') => ({
    tampil: teks,

    init() {
        const cocok = String(teks).match(/^(\D*?)(\d[\d.]*)(.*)$/);
        if (!cocok || kurangiGerak()) {
            return;
        }

        const [, awalan, angkaTeks, akhiran] = cocok;
        const target = parseInt(angkaTeks.replace(/\./g, ''), 10);
        this.tampil = awalan + '0' + akhiran;

        saatTerlihat(this.$el, () => {
            const mulai = performance.now();
            const durasi = 1400;
            const langkah = (sekarang) => {
                const t = Math.min((sekarang - mulai) / durasi, 1);
                const nilai = Math.round(target * (1 - (1 - t) ** 4));
                this.tampil = awalan + nilai.toLocaleString('id-ID') + akhiran;
                if (t < 1) {
                    requestAnimationFrame(langkah);
                } else {
                    this.tampil = teks;
                }
            };
            requestAnimationFrame(langkah);
        }, 0.5);
    },
}));

/**
 * Diagram donat belanja per bidang. Busur digambar di server (Blade); komponen ini hanya mengatur
 * bagian yang disorot (arahkan kursor, ketuk, atau fokus keyboard pada keterangan) dan teks di tengah donat.
 */
Alpine.data('donatAnggaran', (bagian = [], bawaan = {}) => ({
    bagian,
    aktif: null,

    get sorot() {
        return this.aktif === null ? bawaan : this.bagian[this.aktif];
    },

    pilih(i) {
        this.aktif = i;
    },

    lepas() {
        this.aktif = null;
    },

    redup(i) {
        return this.aktif !== null && this.aktif !== i;
    },
}));

window.Alpine = Alpine;
Alpine.start();
