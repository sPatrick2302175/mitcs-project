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
        // Super admin department (none offical department)
        $department = Department::firstOrCreate(
            ['code' => 'SYSTEM-ADMIN'],
            ['department_name' => 'SYSTEM-ADMIN']
        );

        // Super admin division (none offical division)
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
                'position' => 'Super Admin',
                'department_id' => $department->id,
                'division_id' => $division->id,
                'leave_credits' => 0, 
            ]
        );

        // Super Admin User using the linked employee record
        if (!User::where('email', 'admin@company.com')->exists()) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@company.com',
                'password' => Hash::make('12345678'), // changeable
                'is_admin' => 2, // Super Admin role
                'employee_id' => $adminEmployee->id, 
            ]);

            //$this->command->info('System Super Admin account successfully created!');
        } else {
            //$this->command->warn('Super Admin account already exists. Skipping...');
        }
    }
}