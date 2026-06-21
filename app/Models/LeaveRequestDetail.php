<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestDetail extends Model
{
    protected $fillable = [
        'leave_request_id',
        'leave_date',
        'day_fraction',
        'is_with_pay',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'is_with_pay' => 'boolean',
        'day_fraction' => 'decimal:2', 
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}