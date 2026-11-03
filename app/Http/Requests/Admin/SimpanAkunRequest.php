<?php

namespace App\Http\Requests\Admin;

use App\Enums\Modul;
use App\Enums\Peran;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanAkunRequest extends FormRequest
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
        $user = $this->route('user');
        $abaikan = $user instanceof User ? $user->id : null;

        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($abaikan)],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($abaikan)],
            'peran' => ['required', Rule::enum(Peran::class)],
            'modul' => ['nullable', 'array'],
            'modul.*' => ['string', Rule::in(array_map(fn (Modul $m) => $m->value, Modul::untukStaf()))],
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
            'min' => ':attribute minimal :min karakter.',
            'email' => 'Format email belum benar.',
            'unique' => ':attribute sudah dipakai akun lain.',
            'username.regex' => 'Username hanya boleh huruf, angka, titik, garis bawah, dan tanda hubung.',
            'enum' => ':attribute tidak dikenal.',
            'in' => ':attribute tidak dikenal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'Nama lengkap',
            'email' => 'Email',
            'username' => 'Username',
            'peran' => 'Peran',
            'modul' => 'Modul',
            'modul.*' => 'Modul',
        ];
    }
}
