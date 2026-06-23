<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'leave_type_others',
        'date_of_filing',
        'leave_detail_category', 
        'leave_detail_specifics', 
        'working_days_applied',
        'start_date',
        'end_date',
        'commutation_requested', 
        
        // historical balance snapshots for Section 7.A
        'vl_balance_snapshot',
        'sl_balance_snapshot',

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
        'working_days_applied' => 'decimal:4',
        'vl_balance_snapshot' => 'decimal:4',
        'sl_balance_snapshot' => 'decimal:4',
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
        return $this->belongsTo(Employee::class, 'recommending_officer_id');
    }

    public function approvingOfficial()
    {
        return $this->belongsTo(Employee::class, 'approving_official_id');
    }

    public function ledgers()
    {
        // Polymorphic relationship mapping to reference_type and reference_id
        return $this->morphMany(LeaveLedger::class, 'reference');
    }
    /**
     * A leave request can have multiple supporting documents attached.
     */
    public function attachments()
    {
        return $this->hasMany(LeaveAttachment::class);
    }
    /**
     * Get the specific Form No. 6 details associated with this leave request.
     */
    public function details()
    {
        return $this->hasMany(LeaveRequestDetail::class); 
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($q, $search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->where('leave_detail_category', 'like', "%{$search}%")
                     ->orWhere('leave_detail_specifics', 'like', "%{$search}%")
                     ->orWhereHas('leaveType', function ($typeQ) use ($search) {
                         $typeQ->where('leave_type_name', 'like', "%{$search}%")
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
                default => null
            };
        });
    }

    /**
     * Compresses chronological leave dates into a clean, month-grouped format.
     */
    public function getFormattedInclusiveDatesAttribute(): string
    {
        // Gather exact unique days filed from the database breakdown
        $dates = $this->details()
            ->orderBy('leave_date', 'asc')
            ->pluck('leave_date')
            ->map(fn($d) => Carbon::parse($d))
            ->toArray();

        // Fallback safety layer for legacy entries
        if (empty($dates)) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            if ($start->equalTo($end)) {
                return $start->format('M d, Y');
            }
            return $start->format('M d, Y') . ' - ' . $end->format('M d, Y');
        }

        // Group dates by Year, then Month
        $grouped = [];
        foreach ($dates as $date) {
            $year = $date->year;
            $month = $date->format('M'); 
            $day = $date->day;
            $grouped[$year][$month][] = $day;
        }

        $yearStrings = [];
        $totalYears = count($grouped);

        foreach ($grouped as $year => $months) {
            $monthStrings = [];
            foreach ($months as $month => $days) {
                sort($days);
                $ranges = [];
                $start = $days[0];
                $end = $days[0];

                for ($i = 1; $i < count($days); $i++) {
                    if ($days[$i] === $end + 1) {
                        $end = $days[$i];
                    } else {
                        $ranges[] = ($start === $end) ? $start : "$start-$end";
                        $start = $days[$i];
                        $end = $days[$i];
                    }
                }
                $ranges[] = ($start === $end) ? $start : "$start-$end";

                $monthStrings[] = $month . ' ' . implode(', ', $ranges);
            }

            if ($totalYears === 1) {
                $yearStrings[] = implode('; ', $monthStrings);
            } else {
                $yearStrings[] = implode('; ', $monthStrings) . ", $year";
            }
        }

        $finalString = implode('; ', $yearStrings);
        if ($totalYears === 1) {
            $firstYear = array_key_first($grouped);
            $finalString .= ", $firstYear";
        }

        if (strlen($finalString) > 60) {
            return "Various Dates (See Attached Details)";
        }

        return $finalString;
    }
}