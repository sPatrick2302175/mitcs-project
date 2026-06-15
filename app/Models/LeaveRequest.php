<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'date_of_filing',
        'leave_detail_category', 
        'leave_detail_specifics', 
        'working_days_applied',
        'start_date',
        'end_date',
        'commutation_requested', 
        'status', 
        'recommendation_reason', 
        'recommending_officer_id', 
        'approving_official_id', 
        'approved_others', 
        'disapproval_reason', 
        'days_with_pay',
        'days_without_pay',
    ];

    protected $casts = [
        'date_of_filing' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'commutation_requested' => 'boolean',
        'working_days_applied' => 'decimal:1',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function recommendingOfficer()
    {
        // Now accurately points to the employees table
        return $this->belongsTo(Employee::class, 'recommending_officer_id');
    }

    public function approvingOfficial()
    {
        // Now accurately points to the employees table
        return $this->belongsTo(Employee::class, 'approving_official_id');
    }

    public function details()
    {
        return $this->hasMany(LeaveRequestDetail::class);
    }

    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class);
    }

    // Add this inside your LeaveRequest model
    public function ledgers()
    {
        // This tells Laravel: "Find all LeaveLedgers where reference_type is 'App\Models\LeaveRequest' 
        // and reference_id is this request's ID."
        return $this->morphMany(LeaveLedger::class, 'reference');
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($q, $search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->where('leave_detail_category', 'like', "%{$search}%")
                     ->orWhere('leave_detail_specifics', 'like', "%{$search}%")
                     ->orWhereHas('leaveType', function ($typeQ) use ($search) {
                         $typeQ->where('name', 'like', "%{$search}%")
                               ->orWhere('code', 'like', "%{$search}%");
                     })
                     ->orWhereHas('employee', function ($empQ) use ($search) {
                         $empQ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                     });
            });
        });
    }

    public function scopeWithinTimeframe($query, $timeframe)
    {
        return $query->when($timeframe, function ($q, $timeframe) {
            match ($timeframe) {
                'this_month' => $q->whereMonth('date_of_filing', now()->month)
                                  ->whereYear('date_of_filing', now()->year),
                'last_3_months' => $q->where('date_of_filing', '>=', now()->subMonths(3)),
                'this_year' => $q->whereYear('date_of_filing', now()->year),
                default => $q
            };
        });
    }
}