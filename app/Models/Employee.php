<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    
    protected $fillable = [
        'department_id',
        'division_id',
        'employee_id_number',
        'first_name',
        'last_name',
        'middle_initial',
        'position',
        'position_code',
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
        return $this->hasOne(User::class);
    }

    public function leaveRequests()
    {
        // allows an employee profile to access all their historical requests
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalance()
    {
        return $this->hasOne(EmployeeLeaveBalance::class);
    }
}
