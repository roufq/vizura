<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
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
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity_delta' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
            'evidence' => ['nullable', 'file', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location_id.exists' => 'Lokasi tidak ditemukan.',
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'quantity_delta.required' => 'Jumlah penyesuaian wajib diisi.',
            'quantity_delta.numeric' => 'Jumlah penyesuaian harus berupa angka.',
            'quantity_delta.not_in' => 'Jumlah penyesuaian tidak boleh nol.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
            'evidence.file' => 'Bukti harus berupa file.',
            'evidence.max' => 'Bukti maksimal 2MB.',
        ];
    }
}
