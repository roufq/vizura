<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'reference_no' => ['required', 'string', 'max:50', 'unique:purchases,reference_no'],
            'payment_method' => ['required', 'string', 'in:cash,bank,payable'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
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
            'supplier_id.exists' => 'Supplier tidak ditemukan.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'discount_amount.numeric' => 'Diskon harus berupa angka.',
            'tax_amount.numeric' => 'Pajak harus berupa angka.',
            'items.required' => 'Item pembelian wajib diisi.',
            'items.min' => 'Item pembelian wajib diisi.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.quantity.required' => 'Qty wajib diisi.',
            'items.*.quantity.min' => 'Qty minimal 0.01.',
            'items.*.unit_cost.required' => 'HPP wajib diisi.',
            'items.*.unit_cost.min' => 'HPP minimal 0.',
        ];
    }
}
