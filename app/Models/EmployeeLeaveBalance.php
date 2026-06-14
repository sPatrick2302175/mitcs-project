<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'vacation_leave_balance',
        'sick_leave_balance',
        'mandatory_leave_balance',
        'special_privilege_leave_balance',
        'special_emergency_leave_balance',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
