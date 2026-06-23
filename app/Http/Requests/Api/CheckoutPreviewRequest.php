<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutPreviewRequest extends FormRequest
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
            'address_id' => ['required', 'uuid', 'exists:user_addresses,id'],
            'fulfillment_type_code' => ['required', 'integer', 'in:1,2'],
            'note' => ['nullable', 'string', 'max:500'],
            'schedule_at' => ['nullable', 'date', 'after:now'],
            'schedule_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'schedule_slot_code' => ['nullable', 'integer', 'in:1,2,3'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasScheduleAt = $this->filled('schedule_at');
            $hasScheduleDate = $this->filled('schedule_date');
            $hasScheduleSlot = $this->filled('schedule_slot_code');

            if ($hasScheduleAt && ($hasScheduleDate || $hasScheduleSlot)) {
                $validator->errors()->add('schedule_at', 'Use either schedule_at or schedule_date + schedule_slot_code.');
            }

            if ($hasScheduleDate xor $hasScheduleSlot) {
                $validator->errors()->add('schedule_date', 'schedule_date and schedule_slot_code must be provided together.');
            }
        });
    }
}
