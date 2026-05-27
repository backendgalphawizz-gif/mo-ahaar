<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\DriverWallet;
use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminDeliveryPartnerSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        $partners = [
            [
                'driver_code' => 'DP-001',
                'name' => 'Mike Johnson',
                'email' => 'mike@delivery.com',
                'mobile' => '9123456789',
                'city' => 'New York',
                'address' => '123 Main St, NY',
                'approval_status' => 'approved',
                'status' => '1',
                'vehicle_number' => 'MH 01 AB 1234',
                'driving_license_number' => 'DL1234567890',
                'account_holder_name' => 'Mike Johnson',
                'account_number' => '0987654321',
                'bank_name' => 'Chase Bank',
                'branch_name' => 'NY Branch',
                'ifsc_code' => 'CHAS0000123',
                'account_type' => 'current',
                'document_type' => 'aadhar',
                'wallet_balance' => 150,
            ],
            [
                'driver_code' => 'DP-002',
                'name' => 'Sarah Davis',
                'email' => 'sarah@delivery.com',
                'mobile' => '9123456788',
                'city' => 'New York',
                'address' => '456 Market St, NY',
                'approval_status' => 'pending',
                'status' => '0',
                'vehicle_number' => 'MH 02 CD 5678',
                'driving_license_number' => 'DL9876543210',
                'account_holder_name' => 'Sarah Davis',
                'account_number' => '1234567890',
                'bank_name' => 'Chase Bank',
                'branch_name' => 'NY Branch',
                'ifsc_code' => 'CHAS0000123',
                'account_type' => 'savings',
                'document_type' => 'pan',
                'wallet_balance' => 0,
            ],
        ];

        foreach ($partners as $partner) {
            $driver = Users::updateOrCreate(
                [
                    'email' => $partner['email'],
                    'role_type' => Users::DRIVER_APP_ROLE_TYPE,
                ],
                [
                    'name' => $partner['name'],
                    'mobile' => $partner['mobile'],
                    'password' => Hash::make('Driver@123'),
                    'status' => $partner['status'],
                    'approval_status' => $partner['approval_status'],
                ]
            );

            DriverProfile::updateOrCreate(
                ['driver_id' => $driver->user_id],
                [
                    'driver_code' => $partner['driver_code'],
                    'city' => $partner['city'],
                    'address' => $partner['address'],
                    'vehicle_number' => $partner['vehicle_number'],
                    'driving_license_number' => $partner['driving_license_number'],
                    'vehicle_type' => 'Bike',
                    'account_holder_name' => $partner['account_holder_name'],
                    'account_number' => $partner['account_number'],
                    'bank_name' => $partner['bank_name'],
                    'branch_name' => $partner['branch_name'],
                    'ifsc_code' => $partner['ifsc_code'],
                    'account_type' => $partner['account_type'],
                    'document_type' => $partner['document_type'] ?? 'aadhar',
                ]
            );

            if (Schema::hasTable('driver_wallets')) {
                DriverWallet::updateOrCreate(
                    ['driver_id' => $driver->user_id],
                    ['balance' => $partner['wallet_balance'], 'pending_balance' => 0, 'currency' => 'INR']
                );
            }
        }
    }
}
