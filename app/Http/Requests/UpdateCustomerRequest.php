<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('customers')->ignore($customer->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('customers')->ignore($customer->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.unique' => 'Nomor HP sudah digunakan pelanggan lain.',
            'email.unique' => 'Email sudah digunakan pelanggan lain.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
