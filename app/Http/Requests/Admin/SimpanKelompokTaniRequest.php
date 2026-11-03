<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanKelompokTaniRequest extends FormRequest
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
            'komoditas' => ['required', 'integer', Rule::exists('komoditas', 'id')],
            'dusun' => ['required', 'integer', Rule::exists('dusun', 'id')],
            'ketua' => ['nullable', 'string', 'max:100'],
            // Kontak wajib diisi bila dicentang untuk dipublikasikan.
            'kontak' => ['required_if:disetujui,1', 'nullable', 'regex:/^[0-9 +\-]{8,20}$/'],
            'disetujui' => ['nullable', 'boolean'],
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
            'exists' => ':attribute tidak dikenal.',
            'integer' => ':attribute tidak dikenal.',
            'regex' => ':attribute belum berupa nomor telepon yang benar.',
            'required_if' => ':attribute wajib diisi bila dicentang untuk publikasi.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama' => 'Nama kelompok', 'komoditas' => 'Komoditas', 'dusun' => 'Dusun', 'kontak' => 'Nomor kontak', 'ketua' => 'Nama ketua'];
    }
}
