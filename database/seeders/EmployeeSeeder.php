<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Employee 1 in dep and div 1
        Employee::create([
            'department_id' => 1, 
            'division_id' => 1,
            'employee_id_number' => 'EMP-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'position' => 'Admin Officer',
            'leave_credits' => 15
        ]);

        // Sample Employee 2 in dep and div 2
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