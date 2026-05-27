<?php

namespace Database\Seeders;

use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('vendors')) {
            return;
        }

        if (Vendor::count() > 0) {
            return;
        }

        $samples = [
            [
                'owner_name' => 'John Doe',
                'business_name' => 'Tech Superstore',
                'email' => 'john@techstore.com',
                'mobile' => '9876543210',
                'business_phone' => '1122334455',
                'gst_number' => '22AAAAA0000A1Z5',
                'address' => '123 Tech Park, Andheri, Mumbai 400053',
                'bank_name' => 'HDFC Bank',
                'branch_name' => 'Andheri East',
                'bank_account' => '00112233445566',
                'ifsc_code' => 'HDFC0001234',
                'account_type' => 'Current',
                'approval_status' => 'pending',
                'commission_percent' => 15,
                'wallet_balance' => 12450,
                'withdrawal_amount' => 5000,
                'refund_balance' => 1200,
            ],
            [
                'owner_name' => 'Jane Smith',
                'business_name' => 'Fashion Hub',
                'email' => 'jane@fashionhub.com',
                'mobile' => '9876543211',
                'approval_status' => 'approved',
                'commission_percent' => 12,
                'wallet_balance' => 8900,
                'withdrawal_amount' => 2000,
                'refund_balance' => 500,
            ],
            [
                'owner_name' => 'Bob Wilson',
                'business_name' => 'Food Paradise',
                'email' => 'bob@foodparadise.com',
                'mobile' => '9876543212',
                'approval_status' => 'approved',
                'commission_percent' => 10,
                'wallet_balance' => 15600,
                'withdrawal_amount' => 7000,
                'refund_balance' => 900,
            ],
            [
                'owner_name' => 'Alice Brown',
                'business_name' => 'Book World',
                'email' => 'alice@bookworld.com',
                'mobile' => '9876543213',
                'approval_status' => 'suspended',
                'commission_percent' => 8,
                'wallet_balance' => 3200,
                'withdrawal_amount' => 1000,
                'refund_balance' => 200,
            ],
        ];

        foreach ($samples as $index => $sample) {
            $user = Users::create([
                'name' => $sample['owner_name'],
                'email' => $sample['email'],
                'mobile' => $sample['mobile'],
                'password' => Hash::make('Password@123'),
                'role_type' => 3,
                'status' => ($sample['approval_status'] ?? 'pending') === 'approved' ? '1' : '0',
            ]);

            Vendor::create(array_merge($sample, [
                'vendor_code' => 'VEND-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'user_id' => $user->user_id,
                'status' => ($sample['approval_status'] ?? 'pending') === 'approved' ? '1' : '0',
            ]));
        }
    }
}
