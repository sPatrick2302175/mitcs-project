<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestDetail extends Model
{
    protected $guarded = []; // Or define your $fillable array

    protected $casts = [
        'leave_date' => 'date',
        'is_with_pay' => 'boolean',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}