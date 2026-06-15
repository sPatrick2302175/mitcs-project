<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id', 
        'employee_id_number',
        'first_name',
        'last_name',
        'middle_initial',
        'position',
        'position_code',
        'salary', // ADDED: Must be fillable so we can save it to the database
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Traverse upstream: Employee -> Division -> Department
     */
    public function getDepartmentAttribute()
    {
        // This will allow you to call $employee->department safely
        return $this->division ? $this->division->department : null;
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function ledgers()
    {
        return $this->hasMany(LeaveLedger::class);
    }
}