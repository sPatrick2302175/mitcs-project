<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The order here is critical: Departments -> Divisions -> Employees
        $this->call([
            DepartmentSeeder::class,
            DivisionSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}