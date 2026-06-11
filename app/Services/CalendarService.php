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
        return CustomHoliday::all()->flatMap(function ($holiday) {
            $events = [];
            $isRegularColor = ($holiday->type === 'regular'); 
            $title = $holiday->name . ($holiday->is_half_day ? ' (Half-Day)' : '');
            $baseDate = \Carbon\Carbon::parse($holiday->date);

            // If the user checked "Repeats Annually"
            if ($holiday->is_regular) {
                // Loop to create the event for the past 2 years and future 5 years
                for ($i = -2; $i <= 10; $i++) {
                    $events[] = [
                        // Append the year offset so FullCalendar doesn't complain about duplicate IDs
                        'id' => 'custom_' . $holiday->id . '_offset_' . $i, 
                        'title' => $title,
                        'start' => $baseDate->copy()->addYears($i)->format('Y-m-d'),
                        'backgroundColor' => $isRegularColor ? '#3b82f6' : '#f97316',
                        'borderColor' => $isRegularColor ? '#2563eb' : '#ea580c',
                        'textColor' => '#ffffff',
                        'allDay' => true,
                        'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
                    ];
                }
            } else {
                // If it's a one-time event, just push it exactly as you had it
                $events[] = [
                    'id' => 'custom_' . $holiday->id,
                    'title' => $title,
                    'start' => $holiday->date,
                    'backgroundColor' => $isRegularColor ? '#3b82f6' : '#f97316',
                    'borderColor' => $isRegularColor ? '#2563eb' : '#ea580c',
                    'textColor' => '#ffffff',
                    'allDay' => true,
                    'extendedProps' => ['type' => 'custom_holiday', 'holiday_id' => $holiday->id]
                ];
            }

            return $events;
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
            $title = "$employeeName ({$leave->leave_type})";

            foreach ($leave->details as $detail) {
                $events[] = [
                    'id'            => $leave->id,
                    'title'         => $title,
                    'start'         => \Carbon\Carbon::parse($detail->leave_date)->format('Y-m-d'), 
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