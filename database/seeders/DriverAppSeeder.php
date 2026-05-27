<?php

namespace Database\Seeders;

use App\Http\Controllers\API\DriverApp\DriverAppController;
use App\Models\DeliveryAssignment;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DriverAppSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('users_role')) {
            DB::table('users_role')->updateOrInsert(
                ['role_type' => Users::DRIVER_APP_ROLE_TYPE],
                ['role' => 'Driver']
            );
        }

        $driver = Users::updateOrCreate(
            [
                'email' => 'driver@example.com',
                'role_type' => Users::DRIVER_APP_ROLE_TYPE,
            ],
            [
                'name' => 'Amit Sharma',
                'mobile' => '9876543210',
                'password' => Hash::make('Driver@123'),
                'status' => 1,
            ]
        );

        if (!Schema::hasTable('delivery_assignments')) {
            return;
        }

        Orders::query()
            ->orderByDesc('order_id')
            ->limit(5)
            ->get()
            ->each(function (Orders $order) {
                DriverAppController::syncAssignmentFromOrder($order, 700);
            });
    }
}
