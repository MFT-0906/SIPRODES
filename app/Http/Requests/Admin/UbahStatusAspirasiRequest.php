<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UbahStatusAspirasiRequest extends FormRequest
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
            'status' => ['required', 'in:baru,dibaca,selesai'],
            'aksi' => ['nullable', 'in:dibaca,selesai'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['in' => 'Status tidak dikenal.', 'max' => 'Catatan terlalu panjang (maksimal :max karakter).', 'required' => 'Status wajib dipilih.'];
    }
}
