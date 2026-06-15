<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_paid',
        'requires_attachment',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
    ];

    public function balances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}