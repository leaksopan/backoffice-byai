<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHumanResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hrId = $this->route('human_resource');
        
        return [
            'nip' => 'required|string|max:50|unique:mdm_human_resources,nip,' . $hrId,
            'name' => 'required|string|max:255',
            'category' => 'required|in:medis_dokter,medis_perawat,medis_bidan,penunjang_medis,administrasi,umum',
            'position' => 'required|string|max:100',
            'employment_status' => 'required|in:pns,pppk,kontrak,honorer',
            'grade' => 'nullable|string|max:10',
            'basic_salary' => 'nullable|numeric|min:0',
            'effective_hours_per_week' => 'nullable|integer|min:0|max:168',
            'is_active' => 'boolean',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date|after_or_equal:hire_date',
        ];
    }
}
