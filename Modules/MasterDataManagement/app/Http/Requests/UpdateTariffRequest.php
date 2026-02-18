<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.edit');
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:mdm_service_catalogs,id'],
            'service_class' => ['required', Rule::in(['vip', 'kelas_1', 'kelas_2', 'kelas_3', 'umum'])],
            'tariff_amount' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payer_type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'breakdowns' => ['nullable', 'array'],
            'breakdowns.*.component_type' => ['required', Rule::in(['jasa_medis', 'jasa_sarana', 'bmhp', 'obat', 'administrasi'])],
            'breakdowns.*.amount' => ['required', 'numeric', 'min:0'],
            'breakdowns.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Layanan wajib dipilih',
            'service_id.exists' => 'Layanan tidak ditemukan',
            'service_class.required' => 'Kelas layanan wajib dipilih',
            'service_class.in' => 'Kelas layanan tidak valid',
            'tariff_amount.required' => 'Tarif wajib diisi',
            'tariff_amount.min' => 'Tarif tidak boleh negatif',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
            'breakdowns.*.component_type.required' => 'Tipe komponen breakdown wajib diisi',
            'breakdowns.*.component_type.in' => 'Tipe komponen breakdown tidak valid',
            'breakdowns.*.amount.required' => 'Jumlah breakdown wajib diisi',
            'breakdowns.*.amount.min' => 'Jumlah breakdown tidak boleh negatif',
        ];
    }
}
