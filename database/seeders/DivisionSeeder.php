<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division; 

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        Division::create(['division_name' => 'Administrative Division','code' => 'AD-01']);
        Division::create(['division_name' => 'Systems Division','code' => 'SD-01']);
    }
}