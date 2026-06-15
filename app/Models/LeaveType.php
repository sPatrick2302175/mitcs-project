<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'code',
        'leave_type_name', // UPDATED from 'name'
        'is_paid',
        'requires_attachment',
        // NEW CONFIGURATION COLUMNS ADDED:
        'is_cumulative',
        'is_event_based',
        'max_days_per_year',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_cumulative' => 'boolean',
        'is_event_based' => 'boolean',
        'max_days_per_year' => 'decimal:1', // Casts accurately to match the 5,1 decimal in DB
    ];

    public function balances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}