<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveManagementService
{
    public function getPhilippineHolidays(int $year): array
    {
        return Cache::remember("api_ph_holidays_{$year}", now()->addDays(30), function () use ($year) {
            try {
                $response = Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH");
                return $response->successful() ? $response->json() : [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function getBookedDates($queryBuilder): array
    {
        // 1. Get the IDs of the leave requests matching the query (e.g., all pending requests)
        $leaveRequestIds = $queryBuilder->pluck('id');

        // 2. Fetch only the exact, distinct dates associated with those specific requests
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

    public function checkDivisionOverlap(Employee $employee, array $rawDates): bool//companyoverlap
    {
        return LeaveRequestDetail::whereIn('leave_date', $rawDates)
            ->whereHas('leaveRequest', function ($query) use ($employee) {
                // Only look at pending or approved leaves
                $query->whereIn('status', ['pending', 'approved', 'PENDING', 'APPROVED'])
                    ->whereHas('employee', function ($empQuery) use ($employee) {
                        // THIS IS THE MAGIC: Match the division, but exclude the current employee
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

    public function processLeaveTransaction(array $validated, Employee $employee, array $rawDates, array $detailsToInsert)
    {
        $validated['employee_id'] = $employee->id;
        $validated['date_of_filing'] = now();
        $validated['status'] = 'pending';
        $validated['start_date'] = Carbon::parse($rawDates[0])->format('Y-m-d');
        $validated['end_date'] = Carbon::parse(end($rawDates))->format('Y-m-d');

        DB::transaction(function () use ($validated, $detailsToInsert) {
            unset($validated['selected_dates']); 
            $leaveRequest = LeaveRequest::create($validated);

            foreach ($detailsToInsert as &$detail) {
                $detail['leave_request_id'] = $leaveRequest->id;
            }

            if (!empty($detailsToInsert)) {
                LeaveRequestDetail::insert($detailsToInsert);
            }
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
}