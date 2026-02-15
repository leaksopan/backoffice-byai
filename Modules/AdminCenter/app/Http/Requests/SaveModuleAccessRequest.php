<?php

namespace Modules\AdminCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveModuleAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('assignments.manage');
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ];
    }
}
