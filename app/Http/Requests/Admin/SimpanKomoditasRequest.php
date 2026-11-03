<?php

namespace App\Http\Requests\Admin;

use App\Support\UnggahBerkas;
use Illuminate\Foundation\Http\FormRequest;

class SimpanKomoditasRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:100'],
            'musim_panen' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draf,terbit'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'unggulan' => ['nullable', 'boolean'],
            'foto' => UnggahBerkas::aturanGambar(),
            'alt' => ['required_with:foto', 'nullable', 'string', 'max:200'],
            'hapus_foto' => ['nullable', 'boolean'],
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
            'in' => ':attribute tidak dikenal.',
            'required_with' => ':attribute wajib diisi bila foto dipilih.',
        ] + UnggahBerkas::pesanGambar('foto');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama' => 'Nama komoditas', 'musim_panen' => 'Musim panen', 'status' => 'Status', 'alt' => 'Teks alternatif foto'];
    }
}
