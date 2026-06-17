<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'balance',
        'year',
    ];

    protected $casts = [
        'balance' => 'decimal:3',
        'year' => 'integer',
    ];

    // 🌟 THIS TELLS LARAVEL TO INCLUDE OUR VIRTUAL COLUMN WHEN PASSING TO VUE/JSON
    protected $appends = ['display_balance'];

    // 🌟 THE "ZERO" MASK LOGIC
    public function getDisplayBalanceAttribute()
    {
        // If the real balance is negative (e.g. -2.75), return 0. Otherwise, return the real balance.
        return $this->balance < 0 ? 0.000 : (float) $this->balance;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}