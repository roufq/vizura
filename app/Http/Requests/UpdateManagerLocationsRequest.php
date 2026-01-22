<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerLocationsRequest extends FormRequest
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
            'location_ids.array' => 'Lokasi harus berupa daftar.',
            'location_ids.*.integer' => 'Lokasi tidak valid.',
            'location_ids.*.exists' => 'Lokasi tidak ditemukan.',
        ];
    }
}
