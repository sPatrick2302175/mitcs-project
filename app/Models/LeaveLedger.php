<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveLedger extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'type',
        'amount',
        'running_balance',
        
        // Polymorphic reference
        'reference_type', 
        'reference_id',
        
        // Accountability
        'created_by',     
        'reason_code',    
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'running_balance' => 'decimal:3',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    // NEW: The Accountability relationship
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // NEW: The Polymorphic relationship
    public function reference()
    {
        return $this->morphTo();
    }
}