<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:100', 'unique:customers,email'],
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
