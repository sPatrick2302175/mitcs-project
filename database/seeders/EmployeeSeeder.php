<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'division_id' => 1,
            'department_id' => 2, 
            'employee_id_number' => 'EMP-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'position' => 'Admin Officer',
            'leave_credits' => 15
        ]);

        Employee::create([
            'division_id' => 2, 
            'department_id' => 2, 
            'employee_id_number' => 'EMP-1002',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'position' => 'Computer Programmer',
            'leave_credits' => 15
        ]);
    }
}