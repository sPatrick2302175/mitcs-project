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
            
            // Required only if the recommending officer chooses to recommend disapproval
            'recommendation_reason' => 'required_if:status,recommended_for_disapproval|nullable|string',
            
            'days_with_pay' => 'nullable|numeric|min:0',
            'days_without_pay' => 'nullable|numeric|min:0',
            
            // Strictly required if the final action is a disapproval
            'disapproval_reason' => 'required_if:status,disapproved|nullable|string',
        ];
    }
}