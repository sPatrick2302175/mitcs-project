<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Jane goes to HRMS (Dept 1) & Administrative Division (Div 1)
        Employee::create([
            'department_id' => 1, 
            'division_id' => 1,
            'employee_id_number' => 'EMP-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'position' => 'Admin Officer',
            'leave_credits' => 15
        ]);

        // John goes to MITCS (Dept 2) & Systems Division (Div 2)
        Employee::create([
            'department_id' => 2, 
            'division_id' => 2, 
            'employee_id_number' => 'EMP-1002',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'position' => 'Computer Programmer',
            'leave_credits' => 15
        ]);
    }
}