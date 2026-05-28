<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'division_id' => 1, // Links to Software Development
            'department_id' => 1, // Links to Engineering
            'employee_id_number' => 'EMP-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'position' => 'Backend Engineer',
            'leave_credits' => 15
        ]);

        Employee::create([
            'division_id' => 2, // Links to Recruitment
            'department_id' => 2, // Links to HR
            'employee_id_number' => 'EMP-1002',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'position' => 'Talent Acquisition Spec.',
            'leave_credits' => 15
        ]);
    }
}