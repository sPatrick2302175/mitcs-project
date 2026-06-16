<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{   
    use HasFactory;

    protected $fillable = [
        'division_name',
        'code',
        'department_id'
    ];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Added: A division has many employees
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}