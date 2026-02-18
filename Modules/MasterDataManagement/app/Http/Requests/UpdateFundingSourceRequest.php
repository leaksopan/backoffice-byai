<?php

namespace Modules\MasterDataManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFundingSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('master-data-management.edit');
    }

    public function rules(): array
    {
        $sourceId = $this->route('source') ? $this->route('source')->id : null;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('mdm_funding_sources', 'code')->ignore($sourceId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode sumber dana wajib diisi',
            'code.unique' => 'Kode sumber dana sudah digunakan',
            'name.required' => 'Nama sumber dana wajib diisi',
            'type.required' => 'Tipe sumber dana wajib dipilih',
            'type.in' => 'Tipe sumber dana tidak valid',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
        ];
    }
}
