<?php

namespace App\Console\Commands;

use Database\Seeders\DriverDemoDataSeeder;
use Illuminate\Console\Command;

class SeedDriverDemoCommand extends Command
{
    protected $signature = 'driver:seed-demo {--fresh : Clear existing demo driver orders/transactions and re-seed}';

    protected $description = 'Seed demo data for driver app APIs (vendors, orders, assignments, wallet, transactions, notifications)';

    public function handle(): int
    {
        $seeder = new DriverDemoDataSeeder();
        $seeder->setCommand($this);

        if ($this->option('fresh')) {
            $this->info('Clearing existing driver demo data...');
            $seeder->fresh();
        } else {
            $seeder->run();
        }

        $this->newLine();
        $this->table(
            ['Item', 'Value'],
            [
                ['Driver login', DriverDemoDataSeeder::DEMO_DRIVER_EMAIL],
                ['Password', 'Driver@123'],
                ['API base', url('/api/driver-app')],
            ]
        );

        return self::SUCCESS;
    }
}
