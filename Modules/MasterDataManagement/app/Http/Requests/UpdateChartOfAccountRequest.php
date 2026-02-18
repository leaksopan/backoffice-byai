<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.edit');
    }

    public function rules(): array
    {
        $accountId = $this->route('account') ? $this->route('account')->id : null;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mdm_chart_of_accounts', 'code')->ignore($accountId),
                'regex:/^\d-\d{2}-\d{2}-\d{2}-\d{3}$/',
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'parent_id' => ['nullable', 'exists:mdm_chart_of_accounts,id'],
            'level' => ['required', 'integer', 'min:0'],
            'is_header' => ['boolean'],
            'is_active' => ['boolean'],
            'external_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode akun wajib diisi',
            'code.unique' => 'Kode akun sudah digunakan',
            'code.regex' => 'Format kode akun harus X-XX-XX-XX-XXX',
            'name.required' => 'Nama akun wajib diisi',
            'category.required' => 'Kategori akun wajib dipilih',
            'category.in' => 'Kategori akun tidak valid',
            'normal_balance.required' => 'Saldo normal wajib dipilih',
            'normal_balance.in' => 'Saldo normal tidak valid',
            'level.required' => 'Level akun wajib diisi',
            'parent_id.exists' => 'Parent akun tidak ditemukan',
        ];
    }
}
