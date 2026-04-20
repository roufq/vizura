<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$this->route('user')?->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:Owner,Manager,HeadStore,Cashier'],
            'active_location_id' => ['required', 'integer', 'exists:locations,id'],
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
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.min' => 'Password must be at least 8 characters.',
            'role.required' => 'Role is required.',
            'role.in' => 'Invalid role.',
            'active_location_id.required' => 'Active location is required.',
            'active_location_id.exists' => 'Location not found.',
            'location_ids.array' => 'Locations must be an array.',
            'location_ids.*.exists' => 'Location not found.',
        ];
    }
}
