<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150', 'unique:suppliers,name'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.max' => 'Nama supplier maksimal 150 karakter.',
            'name.unique' => 'Nama supplier sudah digunakan.',
            'phone.max' => 'Telepon maksimal 30 karakter.',
            'email.email' => 'Email tidak valid.',
            'email.max' => 'Email maksimal 150 karakter.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }
}
