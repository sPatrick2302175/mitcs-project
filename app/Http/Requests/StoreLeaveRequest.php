<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
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
            'leave_type' => 'required|string',
            'leave_type_others' => 'nullable|string|required_if:leave_type,Others',
            'leave_detail_category' => 'nullable|string',
            'leave_detail_specifics' => 'nullable|string',
            'working_days_applied' => 'required|numeric|min:0.5',
            'selected_dates' => 'required|string',
            'commutation_requested' => 'required|boolean',
        ];
    }
}
