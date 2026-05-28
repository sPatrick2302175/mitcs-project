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
        'position',
        'leave_credits',
        'department_id',
        'division_id'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    //
}
