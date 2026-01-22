<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
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
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'cashier_id' => ['nullable', 'integer', 'exists:users,id'],
            'method' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:draft,posted,voided,returned'],
            'type' => ['nullable', 'string', 'in:sale,return'],
            'all_locations' => ['nullable', 'boolean'],
        ];
    }
}
