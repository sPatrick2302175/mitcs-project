<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Division::create(['name' => 'Management Information Technology and Computer Services', 'code' => 'MITCS']);
        Division::create(['name' => 'Administratative Division', 'code' => 'ADMIN']);
    }
}
