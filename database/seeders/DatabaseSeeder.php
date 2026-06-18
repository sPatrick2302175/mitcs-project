<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LeaveTypesSeeder::class,
        ]);

        // Super admin department (non-official department)
        $department = Department::firstOrCreate(
            ['code' => 'SYSTEM-ADMIN'],
            ['department_name' => 'SYSTEM-ADMIN']
        );

        // Super admin division (non-official division)
        $division = Division::firstOrCreate(
            ['code' => 'SYS-ADMIN'],
            [
                'division_name' => 'SYS-ADMIN',
                'department_id' => $department->id
            ]
        );

        // System Admin creation USING Employee table
        $adminEmployee = Employee::firstOrCreate(
            ['employee_id_number' => '0000000'], 
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'middle_initial' => 'S',
                'position' => 'Super Admin',
                'division_id' => $division->id,
                'salary' => 0.00 
            ]
        );

        // Super Admin User using the linked employee record
        User::updateOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('12345678'), 
                'is_admin' => 2, 
                'employee_id' => $adminEmployee->id, 
            ]
        );
    }
}