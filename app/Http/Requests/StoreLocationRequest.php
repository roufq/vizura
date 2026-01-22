<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:locations,code'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'toko_pusat' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode lokasi wajib diisi.',
            'code.max' => 'Kode lokasi maksimal 50 karakter.',
            'code.unique' => 'Kode lokasi sudah digunakan.',
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.max' => 'Nama lokasi maksimal 150 karakter.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'phone.max' => 'Telepon maksimal 30 karakter.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'toko_pusat.boolean' => 'Toko pusat tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->boolean('toko_pusat')) {
                return;
            }

            if (Location::query()->where('toko_pusat', true)->exists()) {
                $validator->errors()->add('toko_pusat', 'Toko pusat hanya boleh satu.');
            }
        });
    }
}
