<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisAnggaran;
use App\Support\UnggahBerkas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanApbdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Baris rincian yang benar-benar kosong (uraian dan anggaran) dibuang sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $rincian = [];

        foreach (JenisAnggaran::cases() as $jenis) {
            $baris = collect((array) data_get($this->input('rincian'), $jenis->value, []))
                ->filter(fn ($b) => is_array($b))
                ->map(fn (array $b) => ['uraian' => trim((string) ($b['uraian'] ?? '')), 'anggaran' => trim((string) ($b['anggaran'] ?? ''))])
                ->reject(fn (array $b) => $b['uraian'] === '' && $b['anggaran'] === '')
                ->values()
                ->all();

            $rincian[$jenis->value] = $baris;
        }

        $this->merge(['rincian' => $rincian]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $periode = $this->route('id');

        return [
            'tahun' => ['required', 'integer', 'between:2000,2100', Rule::unique('apbdes_periode', 'tahun')->ignore($periode)],
            'periode' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:draf,terbit'],
            'aksi' => ['nullable', 'in:draf,terbit'],
            'rincian' => ['array'],
            'rincian.*' => ['array', 'max:50'],
            'rincian.*.*.uraian' => ['required', 'string', 'max:200'],
            'rincian.*.*.anggaran' => ['nullable', 'regex:/^(\d{1,3}(\.\d{3})+|\d+)$/', 'max:20'],
            'judul_dokumen' => ['required_with:dokumen', 'nullable', 'string', 'max:200'],
            'keterangan_periode' => ['nullable', 'string', 'max:100'],
            'dokumen' => UnggahBerkas::aturanPdf(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka tahun.',
            'between' => ':attribute di luar rentang yang benar.',
            'unique' => 'Periode APBDes untuk tahun ini sudah ada.',
            'regex' => 'Masukkan angka saja tanpa huruf.',
            'max' => ':attribute terlalu panjang.',
            'in' => ':attribute tidak dikenal.',
            'required_with' => ':attribute wajib diisi bila berkas PDF dipilih.',
        ] + UnggahBerkas::pesanPdf('dokumen');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tahun' => 'Tahun', 'periode' => 'Periode', 'status' => 'Status',
            'rincian.*.*.uraian' => 'Uraian rincian', 'rincian.*.*.anggaran' => 'Anggaran',
            'judul_dokumen' => 'Judul dokumen', 'keterangan_periode' => 'Keterangan periode dokumen',
        ];
    }
}
