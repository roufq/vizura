<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('units', 'name')->ignore($this->route('unit')),
            ],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama satuan wajib diisi.',
            'name.max' => 'Nama satuan maksimal 100 karakter.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'abbreviation.max' => 'Singkatan maksimal 20 karakter.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }
}
