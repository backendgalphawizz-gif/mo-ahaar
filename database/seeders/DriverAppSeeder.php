<?php

namespace Database\Seeders;

use App\Http\Controllers\API\DriverApp\DriverAppController;
use App\Models\DeliveryAssignment;
use App\Models\DriverNotification;
use App\Models\DriverWallet;
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

        if (Schema::hasTable('driver_wallets')) {
            DriverWallet::updateOrCreate(
                ['driver_id' => $driver->user_id],
                ['balance' => 1250, 'pending_balance' => 0, 'currency' => 'INR']
            );
        }

        if (Schema::hasTable('driver_notifications')) {
            $samples = [
                ['title' => 'Order Delivered', 'message' => 'Delivered to Amit Sharma', 'type' => 'order_delivered'],
                ['title' => 'New Delivery Assigned', 'message' => 'You have a new delivery request', 'type' => 'new_delivery_assigned'],
                ['title' => 'Order Cancelled', 'message' => 'Order #OID124 was cancelled', 'type' => 'order_cancelled'],
            ];

            foreach ($samples as $sample) {
                DriverNotification::create([
                    'driver_id' => $driver->user_id,
                    'title' => $sample['title'],
                    'message' => $sample['message'],
                    'type' => $sample['type'],
                    'is_read' => false,
                ]);
            }
        }
    }
}
