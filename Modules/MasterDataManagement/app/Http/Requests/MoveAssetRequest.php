<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_location_id' => 'required|exists:mdm_organization_units,id',
            'movement_date' => 'required|date',
            'reason' => 'nullable|string',
            'approved_by' => 'nullable|integer',
        ];
    }
}
