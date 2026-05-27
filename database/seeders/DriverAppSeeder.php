<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DriverAppSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DriverDemoDataSeeder::class,
        ]);
    }
}
