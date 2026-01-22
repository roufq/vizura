<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceivablePaymentRequest extends FormRequest
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
            'method' => ['required', 'string', 'in:cash,bank,transfer,card,ewallet,qris'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:50'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'method.required' => 'Metode pembayaran wajib dipilih.',
            'method.in' => 'Metode pembayaran tidak valid.',
            'amount.required' => 'Jumlah bayar wajib diisi.',
            'amount.min' => 'Jumlah bayar minimal 0.01.',
            'reference_no.max' => 'Referensi maksimal 50 karakter.',
            'paid_at.date' => 'Tanggal bayar tidak valid.',
        ];
    }
}
