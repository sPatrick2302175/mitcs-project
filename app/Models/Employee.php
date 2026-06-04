<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    
    protected $fillable = [
        'employee_id_number',
        'first_name',
        'last_name',
        'middle_initial',
        'position',
        'leave_credits',
        'department_id',
        'division_id',

        // Integrated your groupmate's specific leave balance buckets
        'vacation_leave_balance',
        'sick_leave_balance',
        'mandatory_leave_balance',
        'special_privilege_leave_balance',
        'special_emergency_leave_balance',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function user()
    {
        // An employee might have one user account linked to them
        return $this->hasOne(User::class);
    }

    public function leaveRequests()
    {
        // INTEGRATED: Allows an employee profile to access all their historical requests
        return $this->hasMany(LeaveRequest::class);
    }
}
