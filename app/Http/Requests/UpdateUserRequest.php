<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$this->route('user')?->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:Owner,Manager,KepalaToko,Kasir'],
            'active_location_id' => ['required', 'integer', 'exists:locations,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'active_location_id.required' => 'Lokasi aktif wajib dipilih.',
            'active_location_id.exists' => 'Lokasi tidak ditemukan.',
            'location_ids.array' => 'Lokasi harus berupa daftar.',
            'location_ids.*.exists' => 'Lokasi tidak ditemukan.',
        ];
    }
}
