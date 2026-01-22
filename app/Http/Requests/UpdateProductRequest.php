<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($this->route('product')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'block_when_out_of_stock' => ['nullable', 'boolean'],
            'batch_code' => ['nullable', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 50 karakter.',
            'sku.unique' => 'SKU sudah digunakan.',
            'barcode.max' => 'Barcode maksimal 50 karakter.',
            'barcode.unique' => 'Barcode sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'unit_id.required' => 'Satuan wajib dipilih.',
            'unit_id.exists' => 'Satuan tidak ditemukan.',
            'sale_price.required' => 'Harga jual wajib diisi.',
            'sale_price.numeric' => 'Harga jual harus berupa angka.',
            'cost_price.required' => 'HPP wajib diisi.',
            'cost_price.numeric' => 'HPP harus berupa angka.',
            'is_taxable.boolean' => 'Status pajak tidak valid.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'block_when_out_of_stock.boolean' => 'Status blok stok nol tidak valid.',
            'batch_code.max' => 'Batch maksimal 100 karakter.',
            'expires_at.date' => 'Tanggal kadaluarsa tidak valid.',
        ];
    }
}
