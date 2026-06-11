<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\CustomHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
//use Carbon\Carbon;
use InvalidArgumentException;

class LeaveManagementService
{
    /**
     * Orchestrate the entire leave application process (Validation + Calculation + Insertion)
     */
    public function createLeaveApplication(Employee $employee, array $validated): LeaveRequest
    {
        // 1. Parse and sort the incoming dates
        $rawDates = array_map('trim', explode(',', $validated['selected_dates']));
        sort($rawDates);

        // 2. Run Business Rule Validations
        if ($this->checkPersonalOverlap($employee, $rawDates)) {
            throw new InvalidArgumentException('You have already booked a leave request for one or more of these specific dates.');
        }

        if ($this->checkDivisionOverlap($employee, $rawDates)) {
            throw new InvalidArgumentException('One or more selected dates are already taken by another employee whose leave is approved.');
        }

        $balanceField = $this->getBalanceField($validated['leave_type']);
        if ($balanceField && $employee->$balanceField < $validated['working_days_applied']) {
            throw new InvalidArgumentException("Insufficient balance. You only have {$employee->$balanceField} days left.");
        }

        // 3. Calculate Valid Working Days vs Holidays
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
                    'is_with_pay' => true, 
                    'created_at' => now(), 
                    'updated_at' => now()
                ];
            }
        }

        if ($validated['working_days_applied'] > $validWorkingDays) {
            throw new InvalidArgumentException("Error: You applied for {$validated['working_days_applied']} days, but you only selected {$validWorkingDays} valid working days.");
        }

        // 4. Process DB Transactions
        return $this->processLeaveTransaction($validated, $employee, $rawDates, $detailsToInsert);
    }

    public function getPhilippineHolidays(int $year): array
    {
        // Fetch only ACTIVE holidays set by the admin
        $allHolidays = CustomHoliday::where('is_active', true)->get();
        $mappedHolidays = [];

        foreach ($allHolidays as $holiday) {
            $holidayDate = $holiday->date; // Already a Carbon instance due to model casting

            if ($holiday->is_regular) {
                // Recurrence: Override the calendar year to match the requested year
                $mappedHolidays[] = [
                    'date' => sprintf('%04d-%02d-%02d', $year, $holidayDate->month, $holidayDate->day),
                    'name' => $holiday->name,
                ];
            } else {
                // One-time: Only include if the holiday's specific year matches the requested view year
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
                      ->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED']);
            })->exists();
    }

    public function checkDivisionOverlap(Employee $employee, array $rawDates): bool
    {
        return LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                $query->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED'])
                    ->whereHas('employee', function ($empQuery) use ($employee) {
                        $empQuery->where('division_id', $employee->division_id)
                                 ->where('id', '!=', $employee->id);
                    });
            })->exists();
    }

    public function getBalanceField(string $leaveType): ?string
    {
        return match ($leaveType) {
            'Vacation Leave'           => 'vacation_leave_balance',
            'Sick Leave'               => 'sick_leave_balance',
            'Mandatory/Forced Leave'   => 'mandatory_leave_balance',
            'Special Privilege Leave'  => 'special_privilege_leave_balance',
            'Special Emergency Leave'  => 'special_emergency_leave_balance',
            default                    => null,
        };
    }

    public function processLeaveTransaction(array $validated, Employee $employee, array $rawDates, array $detailsToInsert): LeaveRequest
    {
        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';
        $validated['start_date'] = Carbon::parse($rawDates[0])->format('Y-m-d');
        $validated['end_date'] = Carbon::parse(end($rawDates))->format('Y-m-d');

        return DB::transaction(function () use ($validated, $detailsToInsert) {
            unset($validated['selected_dates']); 
            $leaveRequest = LeaveRequest::create($validated);

            foreach ($detailsToInsert as &$detail) {
                $detail['leave_request_id'] = $leaveRequest->id;
            }

            if (!empty($detailsToInsert)) {
                LeaveRequestDetail::insert($detailsToInsert);
            }

            return $leaveRequest;
        });
    }

    public function deductEmployeeBalance(Employee $employee, string $leaveType, float $daysToDeduct)
    {
        $balanceField = $this->getBalanceField($leaveType);
        if ($balanceField) {
            $employee->$balanceField -= $daysToDeduct;
            $employee->save();
        }
    }

    /**
     * Compile all booked dates and holidays to determine disabled calendar days for an employee.
     */
    public function getLeaveCalendarData(Employee $employee): array
    {
        
        // 1. Fetch employee's own upcoming booked dates
        $myBookedDates = $this->getBookedDates(
            LeaveRequest::where('employee_id', $employee->id)->whereIn('status', ['pending', 'approved'])
        );

        // 2. Build base query for coworkers in the same division
        $divisionQuery = LeaveRequest::whereHas('employee', function($q) use ($employee) {
            $q->where('division_id', $employee->division_id)->where('id', '!=', $employee->id);
        });

        // 3. Separate division leaves into approved vs pending
        $divisionApprovedDates = $this->getBookedDates((clone $divisionQuery)->where('status', 'approved'));
        $divisionPendingDates = $this->getBookedDates((clone $divisionQuery)->where('status', 'pending'));
        
        // 4. UPDATED: Fetch public holidays for a multi-year window (Current Year + Next 2 Years)
        $currentYear = (int) date('Y');
        $holidayDates = [];
        
        for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
            $yearlyHolidays = array_column($this->getPhilippineHolidays($year), 'date');
            $holidayDates = array_merge($holidayDates, $yearlyHolidays);
        }
        
        // 5. Combine and deduplicate everything for the "disabled dates" master array
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