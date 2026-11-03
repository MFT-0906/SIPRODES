<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SimpanBeritaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $unggah = config('desa.unggahan');

        return [
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['required', 'string', 'max:300'],
            'isi_teks' => ['required', 'string', 'max:20000'],
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:50'],
            'gambar' => [
                'nullable', 'file',
                'mimetypes:'.implode(',', $unggah['gambar_mimetypes']),
                'max:'.$unggah['gambar_maks_kb'],
                'dimensions:max_width='.$unggah['gambar_maks_piksel'].',max_height='.$unggah['gambar_maks_piksel'],
            ],
            'alt' => ['required_with:gambar', 'nullable', 'string', 'max:200'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $kb = (int) config('desa.unggahan.gambar_maks_kb');
        $px = (int) config('desa.unggahan.gambar_maks_piksel');

        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'gambar.max' => 'Ukuran gambar maksimal '.round($kb / 1024, 1).' MB.',
            'date' => ':attribute belum berupa tanggal yang benar.',
            'mimetypes' => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'dimensions' => "Dimensi gambar maksimal {$px} × {$px} piksel.",
            'gambar.file' => 'Berkas gambar tidak valid.',
            'required_with' => ':attribute wajib diisi bila gambar utama dipilih.',
            'in' => ':attribute tidak dikenal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Judul berita',
            'excerpt' => 'Ringkasan',
            'isi_teks' => 'Isi berita',
            'tanggal' => 'Tanggal',
            'kategori' => 'Kategori',
            'alt' => 'Teks alternatif',
            'status' => 'Status',
        ];
    }
}
