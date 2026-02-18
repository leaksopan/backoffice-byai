<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:mdm_assets,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:tanah,gedung,peralatan_medis,peralatan_non_medis,kendaraan,inventaris',
            'acquisition_value' => 'required|numeric|min:0',
            'acquisition_date' => 'required|date',
            'useful_life_years' => 'nullable|integer|min:1',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance,units_of_production',
            'residual_value' => 'nullable|numeric|min:0',
            'current_location_id' => 'nullable|exists:mdm_organization_units,id',
            'condition' => 'required|in:baik,rusak_ringan,rusak_berat',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }
}
