<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductIndexRequest extends FormRequest
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
        return [
            'category_id' => ['sometimes', 'uuid', 'exists:product_categories,id'],
            'category_slug' => ['sometimes', 'string', 'max:120', 'exists:product_categories,slug'],
            'search' => ['sometimes', 'string', 'max:150'],
            'q' => ['sometimes', 'string', 'max:150'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'string', 'in:default,random,popular,best_selling,lowest,highest,cheapest,expensive,least_selling'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('price_min') || ! $this->filled('price_max')) {
                return;
            }

            if ((float) $this->input('price_min') <= (float) $this->input('price_max')) {
                return;
            }

            $validator->errors()->add('price_max', 'The price_max must be greater than or equal to price_min.');
        });
    }
}
