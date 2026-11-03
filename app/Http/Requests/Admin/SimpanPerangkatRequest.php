<?php

namespace App\Http\Requests\Admin;

use App\Support\UnggahBerkas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanPerangkatRequest extends FormRequest
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
            'jabatan' => ['required', Rule::exists('jabatan', 'nama')],
            'tampil' => ['nullable', 'boolean'],
            'foto' => UnggahBerkas::aturanGambar(persegi: true),
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
            'exists' => ':attribute tidak dikenal.',
        ] + UnggahBerkas::pesanGambar('foto', persegi: true);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama' => 'Nama lengkap', 'jabatan' => 'Jabatan'];
    }
}
