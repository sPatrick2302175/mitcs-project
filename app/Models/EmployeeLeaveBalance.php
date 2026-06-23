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
        'balance' => 'decimal:4',
        'year' => 'integer',
    ];

    protected $appends = ['display_balance'];

    public function getDisplayBalanceAttribute()
    {
        // If the real balance is negative (e.g. -2.75), return 0. Otherwise, return the real balance???
        return $this->balance < 0 ? 0.0000 : (float) $this->balance;
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