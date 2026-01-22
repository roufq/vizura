<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'reference_no' => ['required', 'string', 'max:50', 'unique:expenses,reference_no'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'payment_account_id' => ['required', 'integer', 'exists:accounts,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reference_no.required' => 'Nomor referensi wajib diisi.',
            'reference_no.unique' => 'Nomor referensi sudah digunakan.',
            'account_id.required' => 'Akun biaya wajib dipilih.',
            'account_id.exists' => 'Akun biaya tidak ditemukan.',
            'payment_account_id.required' => 'Akun pembayaran wajib dipilih.',
            'payment_account_id.exists' => 'Akun pembayaran tidak ditemukan.',
            'amount.required' => 'Jumlah biaya wajib diisi.',
            'amount.min' => 'Jumlah biaya minimal 0.01.',
            'expense_date.required' => 'Tanggal biaya wajib diisi.',
        ];
    }
}
