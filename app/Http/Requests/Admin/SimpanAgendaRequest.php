<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SimpanAgendaRequest extends FormRequest
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
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable', 'date_format:H:i'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
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
            'date_format' => ':attribute belum berupa jam yang benar.',
            'in' => ':attribute tidak dikenal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Judul agenda',
            'tanggal' => 'Tanggal',
            'waktu' => 'Waktu',
            'lokasi' => 'Lokasi',
            'status' => 'Status',
            'deskripsi' => 'Deskripsi',
        ];
    }
}
