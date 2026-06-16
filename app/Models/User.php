<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_EMPLOYEE = 0;
    const ROLE_ADMIN_OFFICER = 1; 
    const ROLE_SUPER_ADMIN = 2;   
    const ROLE_DEPT_HEAD = 3;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id', 
        'is_admin', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Helper method to safely pull the department through the employee profile chain
     */
    public function getDepartmentAttribute()
    {
        return $this->employee?->department;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}