<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessLeaveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,disapproved,recommended_for_approval,recommended_for_disapproval',
            'recommendation_reason' => 'nullable|string',
            'days_with_pay' => 'nullable|numeric|min:0',
            'days_without_pay' => 'nullable|numeric|min:0',
            'disapproval_reason' => 'nullable|string',
        ];
    }
}