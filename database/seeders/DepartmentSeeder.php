<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department; 

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // ID: 1
        Department::create([
            'department_name' => 'Human Resource Management Services Office',
            'code' => 'HRMS-01'
        ]);
        
        // ID: 2
        Department::create([
            'department_name' => 'Management Information Technology Computer Services',
            'code' => 'MITCS-01'
        ]);
    }
}