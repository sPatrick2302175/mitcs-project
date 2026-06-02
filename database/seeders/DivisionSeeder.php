<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division; 

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        // Assigned to HRMS (Department 1)
        Division::create([
            'department_id' => 1, 
            'division_name' => 'Administrative Division',
            'code' => 'AD-01'
        ]);
        
        // Assigned to MITCS (Department 2)
        Division::create([
            'department_id' => 2, 
            'division_name' => 'Systems Division',
            'code' => 'SD-01'
        ]);
    }
}