<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
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
            'reference_no' => ['required', 'string', 'max:50', 'unique:stock_transfers,reference_no'],
            'source_location_id' => ['required', 'integer', 'exists:locations,id', 'different:destination_location_id'],
            'destination_location_id' => ['required', 'integer', 'exists:locations,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id', 'required_with:items.*.quantity'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01', 'required_with:items.*.product_id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference_no.required' => 'Nomor dokumen wajib diisi.',
            'reference_no.unique' => 'Nomor dokumen sudah digunakan.',
            'source_location_id.required' => 'Lokasi sumber wajib dipilih.',
            'source_location_id.exists' => 'Lokasi sumber tidak ditemukan.',
            'source_location_id.different' => 'Lokasi tujuan harus berbeda.',
            'destination_location_id.required' => 'Lokasi tujuan wajib dipilih.',
            'destination_location_id.exists' => 'Lokasi tujuan tidak ditemukan.',
            'items.required' => 'Item transfer wajib diisi.',
            'items.min' => 'Item transfer wajib diisi.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.required_with' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.quantity.required' => 'Qty wajib diisi.',
            'items.*.quantity.required_with' => 'Qty wajib diisi.',
            'items.*.quantity.min' => 'Qty minimal 0.01.',
        ];
    }
}
