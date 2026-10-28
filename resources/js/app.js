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
 * Navigasi bagian halaman: menandai bagian yang sedang terlihat (scrollspy) lewat aria-current.
 */
Alpine.data('sectionNav', (ids = []) => ({
    active: ids[0] ?? null,

    init() {
        if (!('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];

                if (visible) {
                    this.active = visible.target.id;
                }
            },
            { rootMargin: '-10% 0px -70% 0px' },
        );

        ids.forEach((id) => {
            const section = document.getElementById(id);

            if (section) {
                observer.observe(section);
            }
        });
    },
}));

/**
 * Katalog Potensi Desa: tab Pertanian/UMKM (pola tablist ARIA, dapat dioperasikan dengan panah/Home/End)
 * dan pencarian langsung pada kartu yang sedang dimuat. Item pencarian bertanda data-cari.
 * TODO(backend): pencarian lintas halaman perlu dilakukan di server (parameter ?q=).
 */
Alpine.data('katalog', (awal = 'pertanian', daftarTab = ['pertanian', 'umkm']) => ({
    tab: awal,
    q: '',
    kosong: {},

    init() {
        const dariHash = window.location.hash.replace('#', '');

        if (daftarTab.includes(dariHash)) {
            this.tab = dariHash;
        }

        this.$watch('q', () => this.$nextTick(() => this.hitung()));
        this.$watch('tab', () => this.$nextTick(() => this.hitung()));
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

    cocok(teks) {
        const kata = this.q.trim().toLowerCase();

        return kata === '' || (teks ?? '').includes(kata);
    },

    hitung() {
        daftarTab.forEach((nama) => {
            const item = [...document.querySelectorAll(`#panel-${nama} [data-cari]`)];

            this.kosong[nama] = item.length > 0 && item.every((el) => getComputedStyle(el).display === 'none');
        });
    },
}));

window.Alpine = Alpine;
Alpine.start();

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
