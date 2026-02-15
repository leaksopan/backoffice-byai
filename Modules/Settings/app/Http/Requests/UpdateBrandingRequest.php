<?php

namespace Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('settings.edit');
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo_light' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'logo_dark' => ['nullable', 'file', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
        ];
    }
}
