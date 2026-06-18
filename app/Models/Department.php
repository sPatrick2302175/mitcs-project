<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        'code'
    ];
    
    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    /**
     * use hasManyThrough to get all employees in a department.
     */
    public function employees()
    {
        return $this->hasManyThrough(Employee::class, Division::class);
    }
}