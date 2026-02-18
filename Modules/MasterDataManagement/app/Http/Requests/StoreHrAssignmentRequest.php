<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\MasterDataManagement\Models\MdmHumanResource;

class StoreHrAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_id' => 'required|exists:mdm_organization_units,id',
            'allocation_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hrId = $this->route('human_resource');
            $hr = MdmHumanResource::find($hrId);
            
            if ($hr) {
                $currentTotal = $hr->activeAssignments()
                    ->where('id', '!=', $this->route('assignment'))
                    ->sum('allocation_percentage');
                
                $newTotal = $currentTotal + $this->allocation_percentage;
                
                if ($newTotal > 100) {
                    $validator->errors()->add(
                        'allocation_percentage',
                        'Total alokasi melebihi 100%. Sisa alokasi tersedia: ' . (100 - $currentTotal) . '%'
                    );
                }
            }
        });
    }
}
