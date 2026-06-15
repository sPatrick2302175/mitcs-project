<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\CustomHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveLedger;
use App\Models\LeaveAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class LeaveManagementService
{
    public function createLeaveApplication(Employee $employee, array $validated): LeaveRequest
    {
        $rawDates = array_map('trim', explode(',', $validated['selected_dates']));
        sort($rawDates);

        if ($this->checkPersonalOverlap($employee, $rawDates)) {
            throw new InvalidArgumentException('You have already booked a leave request for one or more of these specific dates.');
        }

        if ($this->checkDivisionOverlap($employee, $rawDates)) {
            throw new InvalidArgumentException('One or more selected dates are already taken by another employee whose leave is approved.');
        }

        // 1. Fetch the dynamic Leave Type
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $requestedDays = count($rawDates); // Assuming 1 date = 1 day for simplicity

        // 2. Bypass balance check for Event-Driven leaves, but enforce the max cap limit
        if ($leaveType->is_event_based) {
            if ($leaveType->max_days_per_year && $requestedDays > $leaveType->max_days_per_year) {
                throw new InvalidArgumentException("This request exceeds the maximum allowed {$leaveType->max_days_per_year} days for this event.");
            }
        } 
        // 3. Normal balance check for VL, SL, SPL, Forced Leave
        elseif ($leaveType->is_paid) {
            $balanceRecord = EmployeeLeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->first();

            if (!$balanceRecord || $balanceRecord->balance < $requestedDays) {
                throw new InvalidArgumentException('Insufficient leave balance for this request.');
            }
        }

        $startYear = Carbon::parse($rawDates[0])->year;
        $endYear = Carbon::parse(end($rawDates))->year;
        
        $holidayData = $startYear === $endYear 
            ? $this->getPhilippineHolidays($startYear)
            : array_merge($this->getPhilippineHolidays($startYear), $this->getPhilippineHolidays($endYear));
            
        $holidayStrings = array_map(fn($h) => Carbon::parse($h['date'])->format('Y-m-d'), $holidayData);

        $validWorkingDays = 0;
        $detailsToInsert = [];
        
        foreach ($rawDates as $dateString) {
            if (Carbon::parse($dateString)->isWeekday() && !in_array($dateString, $holidayStrings)) {
                $validWorkingDays++;
                $detailsToInsert[] = [
                    'leave_date' => $dateString, 
                    'day_fraction' => 1.00, 
                    'is_with_pay' => $leaveType->is_paid, // Defaults based on leave type
                    'created_at' => now(), 
                    'updated_at' => now()
                ];
            }
        }

        if ($validated['working_days_applied'] > $validWorkingDays) {
            throw new InvalidArgumentException("Error: You applied for {$validated['working_days_applied']} days, but you only selected {$validWorkingDays} valid working days.");
        }

        // 3. Extract attachments from validated data before DB insertion
        $attachments = $validated['attachments'] ?? [];
        unset($validated['attachments']);
        unset($validated['selected_dates']);

        // Find the VL and SL type IDs dynamically (Assuming codes are 'VL' and 'SL')
        $vlType = LeaveType::where('code', 'VL')->first();
        $slType = LeaveType::where('code', 'SL')->first();

        // Get current balances (default to 0 if none exist yet)
        $vlBalance = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $vlType->id)
            ->value('balance') ?? 0.000;

        $slBalance = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $slType->id)
            ->value('balance') ?? 0.000;

        // Append the snapshots to the validated array before passing it to the transaction
        $validated['vl_balance_snapshot'] = $vlBalance;
        $validated['sl_balance_snapshot'] = $slBalance;

        return $this->processLeaveTransaction($validated, $employee, $rawDates, $detailsToInsert, $attachments);
    }

    public function getPhilippineHolidays(int $year): array
    {
        static $allHolidays = null;

        if ($allHolidays === null) {
            $allHolidays = CustomHoliday::where('is_active', true)->get();
        }

        $mappedHolidays = [];

        foreach ($allHolidays as $holiday) {
            $holidayDate = $holiday->date; 

            if ($holiday->is_regular) {
                $mappedHolidays[] = [
                    'date' => sprintf('%04d-%02d-%02d', $year, $holidayDate->month, $holidayDate->day),
                    'name' => $holiday->name,
                ];
            } else {
                if ($holidayDate->year === $year) {
                    $mappedHolidays[] = [
                        'date' => $holidayDate->format('Y-m-d'),
                        'name' => $holiday->name,
                    ];
                }
            }
        }

        return $mappedHolidays;
    }

    public function getBookedDates($queryBuilder): array
    {
        $leaveRequestIds = $queryBuilder->pluck('id');

        return LeaveRequestDetail::whereIn('leave_request_id', $leaveRequestIds)
            ->pluck('leave_date')
            ->toArray();
    }

    public function checkPersonalOverlap(Employee $employee, array $rawDates): bool
    {
        return LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                      ->whereIn('status', ['pending', 'approved']);
            })->exists();
    }

    public function checkDivisionOverlap(Employee $employee, array $rawDates): bool
    {
        return LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                $query->whereIn('status', ['pending', 'approved'])
                    ->whereHas('employee', function ($empQuery) use ($employee) {
                        $empQuery->where('division_id', $employee->division_id)
                                 ->where('id', '!=', $employee->id);
                    });
            })->exists();
    }

    public function processLeaveTransaction(array $validated, Employee $employee, array $rawDates, array $detailsToInsert, array $attachments): LeaveRequest
    {
        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';
        $validated['start_date'] = Carbon::parse($rawDates[0])->format('Y-m-d');
        $validated['end_date'] = Carbon::parse(end($rawDates))->format('Y-m-d');

        return DB::transaction(function () use ($validated, $detailsToInsert, $attachments) {
            
            $leaveRequest = LeaveRequest::create($validated);

            foreach ($detailsToInsert as &$detail) {
                $detail['leave_request_id'] = $leaveRequest->id;
            }

            if (!empty($detailsToInsert)) {
                LeaveRequestDetail::insert($detailsToInsert);
            }

            // 4. Handle file uploads securely if any attachments exist
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $path = $file->store('leave_attachments', 'public');
                    LeaveAttachment::create([
                        'leave_request_id' => $leaveRequest->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            return $leaveRequest;
        });
    }

    /**
     * Deduct balance and create a strict audit trail in the ledger.
     */
    public function deductEmployeeBalance(Employee $employee, int $leaveTypeId, float $daysToDeduct, int $leaveRequestId): void
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);

        DB::transaction(function () use ($employee, $leaveType, $daysToDeduct, $leaveRequestId) {
            
            // 1. FOR NORMAL LEAVES (VL, SL, SPL, Forced)
            if (!$leaveType->is_event_based) {
                
                $balanceRecord = EmployeeLeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->first();

                $newBalance = $balanceRecord ? ($balanceRecord->balance - $daysToDeduct) : 0.00;

                if ($balanceRecord) {
                    $balanceRecord->update(['balance' => $newBalance]);
                }

                // 🎯 FIX: Use polymorphic tracking for standard leaves
                LeaveLedger::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'type' => 'deduction',
                    'amount' => $daysToDeduct,
                    'running_balance' => $newBalance,
                    'reference_type' => \App\Models\LeaveRequest::class,
                    'reference_id' => $leaveRequestId,
                    'created_by' => auth()->id(), // Logs WHICH admin approved it!
                    'reason_code' => 'APPROVED_LEAVE',
                    'remarks' => 'Approved ' . $leaveType->code . ' Request',
                ]);

            } 
            // 2. FOR EVENT-BASED LEAVES (Maternity, Calamity, VAWC)
            else {
                // 🎯 FIX: Use polymorphic tracking for event-based leaves
                LeaveLedger::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'type' => 'deduction',
                    'amount' => $daysToDeduct,
                    'running_balance' => 0.00, // Balance remains unaffected
                    'reference_type' => \App\Models\LeaveRequest::class,
                    'reference_id' => $leaveRequestId,
                    'created_by' => auth()->id(), 
                    'reason_code' => 'EVENT_LEAVE_USED',
                    'remarks' => 'Used Event Leave: ' . $leaveType->leave_type_name,
                ]);
            }
        });
    }

    public function accrueMonthlyLeaveCredits(): void
    {
        $leaveTypes = LeaveType::whereIn('code', ['VL', 'SL'])->get();

        if ($leaveTypes->isEmpty()) {
            return;
        }

        $employees = Employee::all();

        DB::transaction(function () use ($employees, $leaveTypes) {
            $currentMonthYear = now()->format('F Y'); // e.g. "June 2026"
            $remarksText = "Earned Leave Credit for {$currentMonthYear}";

            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    
                    // 🛡️ SAFETY CHECK: Did we already give them credits this month?
                    $alreadyAccrued = \App\Models\LeaveLedger::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('reason_code', 'MONTHLY_ACCRUAL')
                        ->where('remarks', $remarksText)
                        ->exists();

                    if ($alreadyAccrued) {
                        continue; // Skip to the next one, they already got paid!
                    }

                    // 1. Fetch or create balance
                    $balanceRecord = \App\Models\EmployeeLeaveBalance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveType->id,
                        ],
                        ['balance' => 0.00]
                    );

                    // 2. Add 1.25 credits
                    $newBalance = (float) $balanceRecord->balance + 1.25;
                    $balanceRecord->update(['balance' => $newBalance]);

                    // 3. Create the Ledger Receipt
                    \App\Models\LeaveLedger::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'type' => 'accrual',
                        'amount' => 1.25,
                        'running_balance' => $newBalance,
                        'reference_type' => null, 
                        'reference_id' => null,
                        'created_by' => null,     
                        'reason_code' => 'MONTHLY_ACCRUAL',
                        'remarks' => $remarksText,
                    ]);
                }
            }
        });
    }

    public function getLeaveCalendarData(Employee $employee): array
    {
        $myBookedDates = $this->getBookedDates(
            LeaveRequest::where('employee_id', $employee->id)->whereIn('status', ['pending', 'approved'])
        );

        $divisionQuery = LeaveRequest::whereHas('employee', function($q) use ($employee) {
            $q->where('division_id', $employee->division_id)->where('id', '!=', $employee->id);
        });

        $divisionApprovedDates = $this->getBookedDates((clone $divisionQuery)->where('status', 'approved'));
        $divisionPendingDates = $this->getBookedDates((clone $divisionQuery)->where('status', 'pending'));
        
        $currentYear = (int) date('Y');
        $holidayDates = [];
        
        for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
            $yearlyHolidays = array_column($this->getPhilippineHolidays($year), 'date');
            $holidayDates = array_merge($holidayDates, $yearlyHolidays);
        }
        
        $disabledDates = array_values(array_unique(array_merge(
            $myBookedDates, 
            $divisionApprovedDates, 
            $divisionPendingDates, 
            $holidayDates
        )));

        return [
            'myBookedDates'          => $myBookedDates,
            'divisionApprovedDates'  => $divisionApprovedDates,
            'divisionPendingDates'   => $divisionPendingDates,
            'disabledDates'          => $disabledDates,
        ];
    }
}