<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division; 

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        Division::create(['division_name' => 'Software Development','code' => 'DEV-01']);
        Division::create(['division_name' => 'Recruitment','code' => 'REC-01']);
    }
}