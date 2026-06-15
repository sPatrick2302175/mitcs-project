<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'VL', 'name' => 'Vacation Leave', 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'SL', 'name' => 'Sick Leave', 'is_paid' => true, 'requires_attachment' => true], // Usually requires med cert if > 2 days, but set default behavior here
            ['code' => 'SPL', 'name' => 'Special Privilege Leave', 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'ML', 'name' => 'Mandatory Leave', 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'EL', 'name' => 'Emergency Leave', 'is_paid' => true, 'requires_attachment' => false],
        ];

        foreach ($types as $type) {
            LeaveType::create($type);
        }
    }
}