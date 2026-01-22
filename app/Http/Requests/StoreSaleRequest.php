<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $payments = collect($this->input('payments', []))
            ->filter(fn ($payment): bool => filled($payment['method'] ?? null) || filled($payment['amount'] ?? null) || filled($payment['reference_no'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'payments' => $payments,
        ]);
    }

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
        $draftId = $this->integer('draft_id');

        return [
            'reference_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sales', 'reference_no')->ignore($draftId),
            ],
            'draft_id' => [
                'nullable',
                'integer',
                Rule::exists('sales', 'id')->where('status', 'draft')->where('type', 'sale'),
            ],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_tax_inclusive' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_discount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments.*.amount', 'string', 'max:30'],
            'payments.*.amount' => ['required_with:payments.*.method', 'numeric', 'min:0.01'],
            'payments.*.reference_no' => ['nullable', 'string', 'max:50'],
            'action' => ['required', 'string', 'in:draft,post'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference_no.required' => 'Nomor transaksi wajib diisi.',
            'reference_no.unique' => 'Nomor transaksi sudah digunakan.',
            'items.required' => 'Item penjualan wajib diisi.',
            'items.min' => 'Item penjualan wajib diisi.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak ditemukan.',
            'items.*.quantity.required' => 'Qty wajib diisi.',
            'items.*.quantity.min' => 'Qty minimal 0.01.',
            'items.*.unit_price.required' => 'Harga wajib diisi.',
            'items.*.unit_price.min' => 'Harga minimal 0.',
            'items.*.line_discount.min' => 'Diskon baris minimal 0.',
            'tax_rate.max' => 'Pajak maksimal 100%.',
            'payments.*.method.required_with' => 'Metode bayar wajib diisi.',
            'payments.*.amount.required_with' => 'Jumlah bayar wajib diisi.',
            'payments.*.amount.min' => 'Jumlah bayar minimal 0.01.',
            'action.in' => 'Aksi tidak valid.',
        ];
    }
}
