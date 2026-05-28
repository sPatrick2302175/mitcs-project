<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department; 

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create(['department_name' => 'Engineering','code' => 'ENG-01']);
        Department::create(['department_name' => 'Human Resources','code' => 'HR-01']);
    }
}