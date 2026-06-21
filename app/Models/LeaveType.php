<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'code',
        'leave_type_name', 
        'is_paid',
        'requires_attachment',
        
        'is_cumulative',
        'is_event_based',
        'max_days_per_year',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_cumulative' => 'boolean',
        'is_event_based' => 'boolean',
        'max_days_per_year' => 'decimal:1', 
    ];

    public function balances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}