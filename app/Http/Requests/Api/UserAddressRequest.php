<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $baseRule = $this->isMethod('post') ? ['required'] : ['sometimes'];

        return [
            'label' => [...$baseRule, 'string', 'max:50'],
            'recipient_name' => [...$baseRule, 'string', 'min:3', 'max:150'],
            'phone_number' => [...$baseRule, 'string', 'min:10', 'max:20'],
            'address' => [...$baseRule, 'string'],
            'address_note' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'latitude' => [...$baseRule, 'numeric', 'between:-90,90'],
            'longitude' => [...$baseRule, 'numeric', 'between:-180,180'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
