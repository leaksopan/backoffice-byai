<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.create');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:mdm_organization_units,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['installation', 'department', 'unit', 'section'])],
            'parent_id' => ['nullable', 'exists:mdm_organization_units,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode unit organisasi wajib diisi',
            'code.unique' => 'Kode unit organisasi sudah digunakan',
            'name.required' => 'Nama unit organisasi wajib diisi',
            'type.required' => 'Tipe unit organisasi wajib dipilih',
            'type.in' => 'Tipe unit organisasi tidak valid',
            'parent_id.exists' => 'Parent unit tidak ditemukan',
        ];
    }
}
