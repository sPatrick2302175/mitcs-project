<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // 1. Earned / Cumulative Leaves
            [
                'code' => 'VL', 
                'leave_type_name' => 'Vacation Leave', 
                'is_paid' => true, 
                'requires_attachment' => false,
                'is_cumulative' => true,
                'is_event_based' => false,
                'max_days_per_year' => null // Handled dynamically via monthly 1.25 accrual
            ],
            [
                'code' => 'SL', 
                'leave_type_name' => 'Sick Leave', 
                'is_paid' => true, 
                'requires_attachment' => true, // Default to true; logic can check if > 2 days
                'is_cumulative' => true,
                'is_event_based' => false,
                'max_days_per_year' => null // Handled dynamically via monthly 1.25 accrual
            ],

            // 2. Standard Annual Non-Cumulative Leaves
            [
                'code' => 'FL', 
                'leave_type_name' => 'Mandatory/Forced Leave', 
                'is_paid' => true, 
                'requires_attachment' => false,
                'is_cumulative' => false,
                'is_event_based' => false,
                'max_days_per_year' => 5.0 // Deducted from VL, resets annually
            ],
            [
                'code' => 'SPL', 
                'leave_type_name' => 'Special Privilege Leave', 
                'is_paid' => true, 
                'requires_attachment' => false,
                'is_cumulative' => false,
                'is_event_based' => false,
                'max_days_per_year' => 3.0
            ],
            [
                'code' => 'SOPL', 
                'leave_type_name' => 'Solo Parent Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 7.0
            ],

            // 3. Event-Based / Situational Leaves
            [
                'code' => 'ML', 
                'leave_type_name' => 'Maternity Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 105.0
            ],
            [
                'code' => 'PL', 
                'leave_type_name' => 'Paternity Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 7.0
            ],
            [
                'code' => 'AL', 
                'leave_type_name' => 'Adoption Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 60.0 // Standard max bond window
            ],
            [
                'code' => 'VAWC', 
                'leave_type_name' => '10-Day VAWC Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 10.0
            ],
            [
                'code' => 'SLBW', 
                'leave_type_name' => 'Special Leave Benefits for Women', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 60.0 // Up to 2 months max
            ],
            [
                'code' => 'SEL', 
                'leave_type_name' => 'Special Emergency (Calamity) Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 5.0
            ],

            // 4. Institutional / Career Leaves
            [
                'code' => 'STL', 
                'leave_type_name' => 'Study Leave', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 180.0 // Up to 6 months max
            ],
            [
                'code' => 'REHAB', 
                'leave_type_name' => 'Rehabilitation Privilege', 
                'is_paid' => true, 
                'requires_attachment' => true,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => 180.0 // Up to 6 months max
            ],
            [
                'code' => 'OTHERS', 
                'leave_type_name' => 'Others', 
                'is_paid' => true, // Set to true if "Others" defaults to paid
                'requires_attachment' => false,
                'is_cumulative' => false,
                'is_event_based' => true,
                'max_days_per_year' => null 
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}