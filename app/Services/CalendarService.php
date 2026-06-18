<?php

namespace App\Services;

use App\Models\CustomHoliday;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Format custom holidays into FullCalendar events.
     */
    public function getHolidayEvents(): array
    {
        // Uses single mapping instead of heavy looping; frontend will handle infinite year rendering
        return CustomHoliday::where('is_active', true)->get()->map(function ($holiday) {
            $isRegularColor = ($holiday->type === 'regular'); 
            $title = $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : '');

            return [
                'id' => 'custom_' . $holiday->id,
                'title' => $title,
                'start' => Carbon::parse($holiday->date)->format('Y-m-d'),
                'backgroundColor' => $isRegularColor ? '#3b82f6' : '#f97316',
                'borderColor' => $isRegularColor ? '#2563eb' : '#ea580c',
                'textColor' => '#ffffff',
                'allDay' => true,
                
                // Pass the regular toggle flag to the calendar script
                'is_regular' => (bool)$holiday->is_regular, 
                
                'extendedProps' => [
                    'type' => 'custom_holiday', 
                    'holiday_id' => $holiday->id,
                    'is_regular' => (bool)$holiday->is_regular
                ]
            ];
        })->toArray();
    }

    /**
     * Format leave requests into FullCalendar events.
     */
    public function getLeaveEvents($leaves, ?int $myEmployeeId, bool $isAdminView): array
    {
        $events = [];

        foreach ($leaves as $leave) {
            $status = strtolower($leave->status);

            if ($status === 'disapproved') {
                continue;
            }

            $isMyLeave = ($leave->employee_id === $myEmployeeId);
            $cssClass = '';

            // Determine the correct CSS class based on user role and ownership
            if ($isAdminView || $isMyLeave) {
                // You or Admin: Bright Tailwind colors
                if ($status === 'pending') {
                    $cssClass = 'status-pending';
                } elseif ($status === 'approved') {
                    $cssClass = 'status-approved';
                } 
            } else {
                // Coworker: Muted Slate colors
                if ($status === 'pending') {
                    $cssClass = 'status-coworker-pending';
                } elseif ($status === 'approved') {
                    $cssClass = 'status-coworker-approved';
                }
            }

            $employeeName = $leave->employee->first_name ?? 'Employee';
            $leaveTypeName = $leave->leaveType->leave_type_name ?? 'Leave';
            $title = "$employeeName ($leaveTypeName)";

            foreach ($leave->details as $detail) {
                $events[] = [
                    'id'            => $leave->id,
                    'title'         => $title,
                    'start'         => Carbon::parse($detail->leave_date)->format('Y-m-d'), 
                    'allDay'        => true,
                    'className'     => $cssClass,
                    'extendedProps' => [
                        'type'      => $isAdminView ? 'leave_request' : ($status === 'pending' ? 'pending_leave' : 'approved_leave'),
                        'leave_id'  => $leave->id,
                        'detail_id' => $detail->id ?? null,
                        'status'    => $status
                    ]
                ];
            }
        }

        return $events;
    }
}