<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.create');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:mdm_service_catalogs,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi'])],
            'unit_id' => ['required', 'exists:mdm_organization_units,id'],
            'inacbg_code' => ['nullable', 'string', 'max:50'],
            'standard_duration' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode layanan wajib diisi',
            'code.unique' => 'Kode layanan sudah digunakan',
            'name.required' => 'Nama layanan wajib diisi',
            'category.required' => 'Kategori layanan wajib dipilih',
            'category.in' => 'Kategori layanan tidak valid',
            'unit_id.required' => 'Unit penyedia wajib dipilih',
            'unit_id.exists' => 'Unit penyedia tidak ditemukan',
            'standard_duration.min' => 'Durasi standar minimal 1 menit',
        ];
    }
}
