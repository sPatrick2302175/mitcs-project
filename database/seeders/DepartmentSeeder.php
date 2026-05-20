<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create(['division_id' => 1, 'name' => 'Software Development']);
        Department::create(['division_id' => 1, 'name' => 'Network & Infrastructure']);

        Department::create(['division_id' => 2, 'name' => 'Human Resources']);
    }
}
