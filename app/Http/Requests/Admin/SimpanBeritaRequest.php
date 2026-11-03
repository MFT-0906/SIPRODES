<?php

namespace App\Http\Requests\Admin;

use App\Support\UnggahBerkas;
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
        return [
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['required', 'string', 'max:300'],
            'isi_teks' => ['required', 'string', 'max:20000'],
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', 'string', 'max:50'],
            'gambar' => UnggahBerkas::aturanGambar(),
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
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang (maksimal :max karakter).',
            'date' => ':attribute belum berupa tanggal yang benar.',
            'required_with' => ':attribute wajib diisi bila gambar utama dipilih.',
            'in' => ':attribute tidak dikenal.',
        ] + UnggahBerkas::pesanGambar('gambar');
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
