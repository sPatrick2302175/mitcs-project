<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => 'required|exists:leave_types,id',
            'leave_detail_category' => 'nullable|string',
            'leave_type_others' => 'nullable|string|max:255',
            'leave_detail_specifics' => 'nullable|string',
            'working_days_applied' => 'required|numeric|min:0.5',
            'selected_dates' => 'required|string',
            'commutation_requested' => 'required|boolean',

            // Accept the manually typed salary
            'salary' => 'required|numeric|min:1',
            
            // Attachment validation
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max per file
        ];
    }

}