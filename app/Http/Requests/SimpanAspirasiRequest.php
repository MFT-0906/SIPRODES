<?php

namespace App\Http\Requests;

use App\Support\AntiSpamAspirasi;
use App\Support\Turnstile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SimpanAspirasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Bot yang mengisi kolom jebakan dianggap berhasil (tanpa menyimpan apa pun) agar tidak belajar menghindarinya.
     */
    public function jebakanTerisi(): bool
    {
        return filled($this->input(AntiSpamAspirasi::KOLOM_JEBAKAN));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // NIK tidak diminta: formulir hanya menerima nama dan pesan. Isian lain (termasuk "nik") diabaikan.
            'nama' => ['required', 'string', 'max:100'],
            'pesan' => ['required', 'string', 'min:20', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama paling banyak :max karakter.',
            'pesan.required' => 'Isi pesan wajib diisi.',
            'pesan.min' => 'Pesan terlalu pendek. Tulis minimal :min karakter.',
            'pesan.max' => 'Pesan terlalu panjang. Tulis paling banyak :max karakter.',
        ];
    }

    /**
     * Pemeriksaan token waktu dan jawaban hitungan; pesan galat bersifat umum dan tidak membocorkan cara kerja anti-spam.
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->jebakanTerisi()) {
                return;
            }

            $galat = AntiSpamAspirasi::periksaToken($this->input(AntiSpamAspirasi::KOLOM_TOKEN));
            if ($galat !== null) {
                $v->errors()->add('formulir', $galat);
            }

            // Verifikasi manusia: Cloudflare Turnstile bila aktif dan widgetnya berhasil dimuat; selain itu
            // (Turnstile tidak aktif, widget gagal dimuat, atau Cloudflare tidak dapat dihubungi) pertanyaan hitungan.
            $tokenTurnstile = (string) $this->input(Turnstile::KOLOM_TOKEN, '');
            if (Turnstile::aktif() && $tokenTurnstile !== '') {
                $hasil = Turnstile::periksa($tokenTurnstile);
                if ($hasil === true) {
                    session()->forget(AntiSpamAspirasi::SESI_JAWABAN);

                    return;
                }
                if ($hasil === false) {
                    session()->forget(AntiSpamAspirasi::SESI_JAWABAN);
                    $v->errors()->add('formulir', 'Verifikasi keamanan belum berhasil. Muat ulang halaman lalu coba lagi.');

                    return;
                }
            }

            $galatJawaban = AntiSpamAspirasi::periksaJawaban($this->input(AntiSpamAspirasi::KOLOM_JAWABAN));
            if ($galatJawaban !== null) {
                $v->errors()->add(AntiSpamAspirasi::KOLOM_JAWABAN, $galatJawaban);
            }
        });
    }
}
