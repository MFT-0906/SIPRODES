<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

# Aturan Frontend — Website Desa Marga Agung

Acuan: `docs/planFE.md` (rencana fase), `DESIGN.md`, dan SKPL. Desain: file Figma **Website Desa Marga Agung — UI Design V2** (`https://www.figma.com/design/ajsIaMebhpIfIRfdkqHFDo`). Jika token DESIGN.md berbeda dengan Figma, ikuti Figma/planFE.md.

- Stack: Laravel + Blade + Vite + **Tailwind CSS saja** (tanpa Bootstrap). JS seminimal mungkin; Alpine.js boleh untuk menu, tab, dan dialog.
- Satu fase per sesi, urut sesuai `docs/planFE.md`. Selesai fase: centang tugas, isi Log, ubah status, `npm run build` tanpa error, commit `feat(fe): fase N — <ringkasan>`.
- Ambil konteks desain dari Figma MCP per node, termasuk anotasi frame (aturan data, mis. kontak tampil setelah diverifikasi).
- Jangan mengarang data. Data sementara hanya di `app/Support/DemoContent.php` dengan komentar `// TODO(backend): ganti dengan data dari database`; jangan menulis data langsung di Blade.
- Tanpa foto AI/stok; pakai komponen placeholder gambar. Setiap `<img>` wajib punya `alt`.
- Bahasa Indonesia baku untuk semua teks antarmuka.
- Aksesibilitas: label form terhubung ke input, fokus keyboard terlihat, target sentuh ≥ 44×44 px, status tidak hanya dibedakan warna, kontras ≥ 4,5:1.
- Mobile-first (mulai 390 px); breakpoint mengikuti DESIGN.md §8.
- Jangan membuat kontrol palsu: yang belum tersambung backend diberi `// TODO(backend)` dan tidak error.
- Keamanan dan izin ditegakkan di backend; jangan menaruh logika izin atau rahasia di JavaScript.
