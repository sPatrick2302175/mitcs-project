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
            'leave_detail_specifics' => 'nullable|string',
            'working_days_applied' => 'required|numeric|min:0.5',
            'selected_dates' => 'required|string',
            'commutation_requested' => 'required|boolean',
            
            // Attachment validation
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max per file
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $leaveTypeId = $this->input('leave_type_id');
            
            if ($leaveTypeId) {
                $leaveType = LeaveType::find($leaveTypeId);
                
                // If the dynamic leave type requires a file, ensure it was uploaded
                if ($leaveType && $leaveType->requires_attachment && !$this->hasFile('attachments')) {
                    $validator->errors()->add('attachments', "An attachment (like a Medical Certificate) is strictly required for {$leaveType->name}.");
                }
            }
        });
    }
}