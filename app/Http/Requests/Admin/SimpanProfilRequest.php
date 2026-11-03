<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi per tab Profil Desa. Tab yang tidak dikenal menghasilkan 404 (diperiksa di controller).
 */
class SimpanProfilRequest extends FormRequest
{
    public const TAB = ['sejarah', 'visi', 'lokasi', 'data'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return match ($this->route('tab')) {
            'sejarah' => ['sejarah' => ['required', 'string', 'max:20000']],
            'visi' => [
                'visi' => ['required', 'string', 'max:500'],
                'misi' => ['nullable', 'array', 'max:20'],
                'misi.*' => ['nullable', 'string', 'max:300'],
            ],
            'lokasi' => [
                'alamat' => ['required', 'string', 'max:300'],
                'lintang' => ['required', 'numeric', 'between:-90,90'],
                'bujur' => ['required', 'numeric', 'between:-180,180'],
                'telepon' => ['nullable', 'regex:/^[0-9 ()+\-]{5,20}$/'],
                'email' => ['nullable', 'email', 'max:150'],
                'terverifikasi' => ['nullable', 'boolean'],
                'aksi' => ['nullable', 'in:draf,terbit'],
            ],
            'data' => [
                'penduduk' => ['required', 'integer', 'min:0', 'max:100000000'],
                'kk' => ['required', 'integer', 'min:0', 'max:100000000'],
                'luas' => ['required', 'integer', 'min:0', 'max:100000000'],
                'dusun' => ['required', 'integer', 'min:0', 'max:1000'],
                'sumber' => ['required', 'string', 'max:150'],
                'terverifikasi' => ['nullable', 'boolean'],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute terlalu panjang atau terlalu besar.',
            'numeric' => ':attribute harus berupa angka koordinat.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute tidak boleh negatif.',
            'between' => ':attribute di luar rentang yang benar.',
            'regex' => ':attribute belum berupa nomor telepon yang benar.',
            'email' => ':attribute belum berupa alamat email yang benar.',
            'in' => ':attribute tidak dikenal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sejarah' => 'Naskah sejarah', 'visi' => 'Visi', 'misi.*' => 'Butir misi',
            'alamat' => 'Alamat kantor desa', 'lintang' => 'Lintang', 'bujur' => 'Bujur',
            'telepon' => 'Telepon kantor', 'email' => 'Email resmi',
            'penduduk' => 'Jumlah penduduk', 'kk' => 'Kepala keluarga', 'luas' => 'Luas wilayah',
            'dusun' => 'Jumlah dusun', 'sumber' => 'Sumber data',
        ];
    }
}
