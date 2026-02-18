<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.edit');
    }

    public function rules(): array
    {
        $unitId = $this->route('unit');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('mdm_organization_units', 'code')->ignore($unitId)],
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
