<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id',
        'date_of_filing',
        
        // 6.A TYPE OF LEAVE
        'leave_type', 
        'leave_type_others', 
        
        // 6.B DETAILS OF LEAVE
        'leave_detail_category', 
        'leave_detail_specifics', 
        
        // 6.C NUMBER OF WORKING DAYS APPLIED FOR
        'working_days_applied',
        'start_date',
        'end_date',
        
        // 6.D COMMUTATION
        'commutation_requested', 
        
        // 7. DETAILS OF ACTION ON APPLICATION
        'status', 
        
        // 7.B RECOMMENDATION
        'recommendation_reason', 
        'recommending_officer_id', 
        
        // 7.C & 7.D FINAL ACTION
        'days_with_pay',
        'days_without_pay',
        'approved_others', 
        'disapproval_reason', 
        'approving_official_id', 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_of_filing' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'commutation_requested' => 'boolean',
        'working_days_applied' => 'decimal:1',
    ];

    /**
     * Get the employee who submitted the leave request.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the officer who recommended the action (Section 7.B).
     */
    public function recommendingOfficer()
    {
        return $this->belongsTo(User::class, 'recommending_officer_id');
    }

    /**
     * Get the official who gave final approval/disapproval (Section 7.C / 7.D).
     */
    public function approvingOfficial()
    {
        return $this->belongsTo(User::class, 'approving_official_id');
    }
}