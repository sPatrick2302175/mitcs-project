<?php

namespace App\Services;

use App\Models\CustomHoliday;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Format custom holidays into FullCalendar events.
     */
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
                for ($i = -2; $i <= 5; $i++) {
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

            if ($status === 'disapproved' || $status === 'rejected') {
                continue; 
            }

            $isPending = $status === 'pending';
            
            if ($isAdminView) {
                $bgColor = $isPending ? '#fff2cb' : ($status === 'approved' ? '#c1f7d5' : '#ffffff');
                $bdColor = $isPending ? '#ca8a04' : ($status === 'approved' ? '#16a34a' : '#dc2626');
                $textColor = '#6e6e6e'; 
            } else {
                $isMyLeave = ($leave->employee_id === $myEmployeeId);
                $bgColor = $isMyLeave ? ($isPending ? '#eab308' : '#22c55e') : ($isPending ? '#94a3b8' : '#64748b');
                $bdColor = $isMyLeave ? ($isPending ? '#ca8a04' : '#16a34a') : ($isPending ? '#64748b' : '#475569');
                $textColor = '#ffffff';
            }

            $employeeName = $leave->employee->first_name ?? 'Employee';
            $title = $isAdminView ? "$employeeName - {$leave->leave_type}" : "$employeeName ({$leave->leave_type})";

            foreach ($leave->details as $detail) {
                $events[] = [
                    'id'              => $leave->id,
                    'title' => $title,
                    'start' => Carbon::parse($detail->leave_date)->format('Y-m-d'), 
                    'backgroundColor' => $bgColor,
                    'borderColor' => $bdColor,
                    'textColor' => $textColor,
                    'allDay' => true,
                    'extendedProps' => [
                        'type' => $isAdminView ? 'leave_request' : ($isPending ? 'pending_leave' : 'approved_leave'),
                        'leave_id' => $leave->id,
                        'detail_id' => $detail->id ?? null,
                        'status' => $status
                    ]
                ];
            }
        }

        return $events;
    }
}