<?php

namespace App\Http\Requests\Admin;

use App\Support\Format;
use App\Support\UnggahBerkas;
use Illuminate\Foundation\Http\FormRequest;

class SimpanUmkmRequest extends FormRequest
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
            'produk' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:300'],
            'whatsapp' => ['nullable', 'regex:/^[0-9 +\-]{8,20}$/', function (string $atribut, mixed $nilai, \Closure $gagal) {
                if (Format::nomorWa((string) $nilai) === null) {
                    $gagal(':attribute belum berupa nomor WhatsApp yang benar.');
                }
            }],
            'disetujui' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:draf,terbit'],
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
            'regex' => ':attribute belum berupa nomor telepon yang benar.',
            'required_with' => ':attribute wajib diisi bila foto dipilih.',
        ] + UnggahBerkas::pesanGambar('foto');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama' => 'Nama usaha', 'produk' => 'Produk', 'deskripsi' => 'Deskripsi', 'whatsapp' => 'Nomor WhatsApp', 'alt' => 'Teks alternatif foto'];
    }
}
