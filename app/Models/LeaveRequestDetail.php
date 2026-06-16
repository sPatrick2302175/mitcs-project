<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestDetail extends Model
{
    // UPDATED: Swapped $guarded for strict $fillable for security
    protected $fillable = [
        'leave_request_id',
        'leave_date',
        'day_fraction',
        'is_with_pay',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'is_with_pay' => 'boolean',
        'day_fraction' => 'decimal:2', // ADDED: Ensures precise 0.50 or 1.00 casting
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}