<?php

namespace Database\Seeders;

use App\Models\Customers;
use App\Models\DeliveryAssignment;
use App\Models\DriverNotification;
use App\Models\DriverProfile;
use App\Models\DriverTransaction;
use App\Models\DriverWallet;
use App\Models\DriverWithdrawal;
use App\Models\Orders;
use App\Models\StaticPage;
use App\Models\StoreSetting;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DriverDemoDataSeeder extends Seeder
{
    public const DEMO_DRIVER_EMAIL = 'driver@example.com';

    public function run(): void
    {
        $this->seedRoles();
        $driver = $this->seedDriver();
        $vendors = $this->seedVendors();
        $customers = $this->seedCustomers();
        $this->seedStaticPages();
        $this->seedStoreSettings();
        $this->seedDriverProfile($driver);
        $this->seedOrdersAndAssignments($driver, $vendors, $customers);
        $this->seedWalletAndTransactions($driver);
        $this->seedNotifications($driver);

        $this->command?->info('Driver demo data seeded successfully.');
        $this->command?->info('Login: ' . self::DEMO_DRIVER_EMAIL . ' / Driver@123');
    }

    public function fresh(): void
    {
        $driver = Users::where('email', self::DEMO_DRIVER_EMAIL)
            ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
            ->first();

        if ($driver) {
            $driverId = (int) $driver->user_id;

            if (Schema::hasTable('driver_transactions')) {
                DriverTransaction::where('driver_id', $driverId)->delete();
            }
            if (Schema::hasTable('driver_withdrawals')) {
                DriverWithdrawal::where('driver_id', $driverId)->delete();
            }
            if (Schema::hasTable('driver_notifications')) {
                DriverNotification::where('driver_id', $driverId)->delete();
            }
            if (Schema::hasTable('delivery_assignment_rejections')) {
                DB::table('delivery_assignment_rejections')
                    ->whereIn('assignment_id', DeliveryAssignment::where('driver_id', $driverId)->pluck('assignment_id'))
                    ->delete();
            }
            if (Schema::hasTable('delivery_assignments')) {
                DeliveryAssignment::where('driver_id', $driverId)->delete();
            }
        }

        Orders::where('order_number', 'like', 'DEMO-%')->delete();

        $this->run();
    }

    private function seedRoles(): void
    {
        if (!Schema::hasTable('users_role')) {
            return;
        }

        DB::table('users_role')->updateOrInsert(
            ['role_type' => Users::DRIVER_APP_ROLE_TYPE],
            ['role' => 'Driver']
        );
    }

    private function seedDriver(): Users
    {
        return Users::updateOrCreate(
            [
                'email' => self::DEMO_DRIVER_EMAIL,
                'role_type' => Users::DRIVER_APP_ROLE_TYPE,
            ],
            [
                'name' => 'Amit Sharma',
                'mobile' => '9876543210',
                'password' => Hash::make('Driver@123'),
                'status' => 1,
            ]
        );
    }

    /**
     * @return array<int, Vendor>
     */
    private function seedVendors(): array
    {
        if (!Schema::hasTable('vendors')) {
            return [];
        }

        $samples = [
            [
                'vendor_code' => 'VEND-DEMO-001',
                'business_name' => "Domino's Pizza",
                'owner_name' => 'Rajesh Kumar',
                'email' => 'dominos.demo@moahaar.local',
                'mobile' => '9811111111',
                'address' => 'Medanta Hospital Road, Sector 38, Gurugram, Haryana 122001',
                'city' => 'Gurugram',
                'gst_number' => '06AAAAA0000A1Z5',
                'approval_status' => 'approved',
                'status' => '1',
            ],
            [
                'vendor_code' => 'VEND-DEMO-002',
                'business_name' => 'Burger King',
                'owner_name' => 'Priya Singh',
                'email' => 'burgerking.demo@moahaar.local',
                'mobile' => '9822222222',
                'address' => 'DLF Cyber Hub, Gurugram, Haryana 122002',
                'city' => 'Gurugram',
                'approval_status' => 'approved',
                'status' => '1',
            ],
            [
                'vendor_code' => 'VEND-DEMO-003',
                'business_name' => 'Haldiram\'s',
                'owner_name' => 'Vikram Mehta',
                'email' => 'haldirams.demo@moahaar.local',
                'mobile' => '9833333333',
                'address' => 'MG Road, Gurugram, Haryana 122001',
                'city' => 'Gurugram',
                'approval_status' => 'approved',
                'status' => '1',
            ],
        ];

        $vendors = [];
        foreach ($samples as $sample) {
            $city = $sample['city'];
            unset($sample['city']);

            $vendors[] = Vendor::updateOrCreate(
                ['vendor_code' => $sample['vendor_code']],
                array_merge($sample, [
                    'commission_percent' => 12,
                    'wallet_balance' => 5000,
                ])
            );
        }

        return $vendors;
    }

    /**
     * @return array<int, Customers>
     */
    private function seedCustomers(): array
    {
        if (!Schema::hasTable('customers')) {
            return [];
        }

        $samples = [
            ['name' => 'Shreya Shah', 'email' => 'shreya.demo@moahaar.local', 'mobile' => '9517530286', 'address' => 'Sector 45, Gurugram, Haryana'],
            ['name' => 'Rahul Verma', 'email' => 'rahul.demo@moahaar.local', 'mobile' => '9123456780', 'address' => 'Sohna Road, Gurugram, Haryana'],
            ['name' => 'Neha Gupta', 'email' => 'neha.demo@moahaar.local', 'mobile' => '9988776655', 'address' => 'South City 1, Gurugram, Haryana'],
            ['name' => 'Amit Sharma', 'email' => 'amit.customer.demo@moahaar.local', 'mobile' => '9876501234', 'address' => 'Palam Vihar, Gurugram, Haryana'],
        ];

        $customers = [];
        foreach ($samples as $sample) {
            $user = Users::updateOrCreate(
                [
                    'email' => $sample['email'],
                    'role_type' => Users::CUSTOMER_APP_ROLE_TYPE,
                ],
                [
                    'name' => $sample['name'],
                    'mobile' => $sample['mobile'],
                    'password' => Hash::make('Customer@123'),
                    'status' => 1,
                    'approval_status' => 'approved',
                ]
            );

            $customers[] = Customers::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'customer_address' => $sample['address'],
                    'gender' => 'Other',
                ]
            );
        }

        return $customers;
    }

    private function seedStaticPages(): void
    {
        if (!Schema::hasTable('static_pages')) {
            return;
        }

        $pages = [
            ['slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'content' => '<p>Mo Aahar driver app privacy policy demo content.</p>'],
            ['slug' => 'terms-and-conditions', 'title' => 'Terms and Conditions', 'content' => '<p>Mo Aahar driver app terms and conditions demo content.</p>'],
            ['slug' => 'faqs', 'title' => 'FAQs', 'content' => '<p><strong>How do I accept a delivery?</strong><br>Go to Home and tap Accept on a new delivery.</p>'],
            ['slug' => 'driver-privacy-policy', 'title' => 'Privacy Policy', 'content' => '<p>Mo Aahar driver app privacy policy demo content.</p>'],
            ['slug' => 'driver-terms-and-conditions', 'title' => 'Terms and Conditions', 'content' => '<p>Mo Aahar driver app terms and conditions demo content.</p>'],
            ['slug' => 'driver-faqs', 'title' => 'FAQs', 'content' => '<p><strong>How do I accept a delivery?</strong><br>Go to Home and tap Accept on a new delivery.</p>'],
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'status' => 1,
                ]
            );
        }
    }

    private function seedStoreSettings(): void
    {
        if (!Schema::hasTable('store_settings')) {
            return;
        }

        $data = [
            'app_name' => 'Mo Aahar',
            'support_email' => 'support@moahaar.com',
            'support_number' => '1800123456',
        ];

        if (Schema::hasColumn('store_settings', 'driver_app_privacy_policy_enabled')) {
            $data['driver_app_privacy_policy_enabled'] = true;
            $data['driver_app_terms_enabled'] = true;
            $data['driver_app_faq_enabled'] = true;
        }

        StoreSetting::query()->firstOrCreate([], $data);
    }

    private function seedDriverProfile(Users $driver): void
    {
        if (!Schema::hasTable('driver_profiles')) {
            return;
        }

        DriverProfile::updateOrCreate(
            ['driver_id' => $driver->user_id],
            [
                'driver_code' => 'DRV-DEMO-001',
                'city' => 'Gurugram',
                'address' => 'Sector 14, Gurugram, Haryana',
                'account_holder_name' => 'Amit Sharma',
                'bank_name' => 'HDFC Bank',
                'branch_name' => 'Gurugram Main',
                'account_number' => '50100123456789',
                'ifsc_code' => 'HDFC0001234',
                'account_type' => 'savings',
                'vehicle_number' => 'MP 09 AB 0000',
                'vehicle_type' => 'Bike',
                'vehicle_model' => 'Hero Splendor',
                'vehicle_color' => 'Black',
                'registration_year' => 2022,
                'driving_license_number' => 'DL-0123456789',
                'driving_license_uploaded_at' => now()->subDays(10),
                'aadhar_card_uploaded_at' => now()->subDays(10),
            ]
        );
    }

    /**
     * @param  array<int, Vendor>  $vendors
     * @param  array<int, Customers>  $customers
     */
    private function seedOrdersAndAssignments(Users $driver, array $vendors, array $customers): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasTable('delivery_assignments') || empty($vendors) || empty($customers)) {
            return;
        }

        $driverId = (int) $driver->user_id;

        $scenarios = [
            ['num' => '001', 'status' => DeliveryAssignment::STATUS_NEW, 'driver_id' => null, 'order_status' => 'processing', 'payout' => 700],
            ['num' => '002', 'status' => DeliveryAssignment::STATUS_NEW, 'driver_id' => null, 'order_status' => 'shipped', 'payout' => 850],
            ['num' => '003', 'status' => DeliveryAssignment::STATUS_NEW, 'driver_id' => null, 'order_status' => 'out_for_delivery', 'payout' => 650],
            ['num' => '004', 'status' => DeliveryAssignment::STATUS_ASSIGNED, 'driver_id' => $driverId, 'order_status' => 'processing', 'payout' => 700],
            ['num' => '005', 'status' => DeliveryAssignment::STATUS_ASSIGNED, 'driver_id' => $driverId, 'order_status' => 'shipped', 'payout' => 900],
            ['num' => '006', 'status' => DeliveryAssignment::STATUS_PICKED_UP, 'driver_id' => $driverId, 'order_status' => 'picked_up', 'payout' => 750],
            ['num' => '007', 'status' => DeliveryAssignment::STATUS_OUT_FOR_DELIVERY, 'driver_id' => $driverId, 'order_status' => 'out_for_delivery', 'payout' => 800],
            ['num' => '008', 'status' => DeliveryAssignment::STATUS_DELIVERED, 'driver_id' => $driverId, 'order_status' => 'delivered', 'payout' => 1053],
            ['num' => '009', 'status' => DeliveryAssignment::STATUS_DELIVERED, 'driver_id' => $driverId, 'order_status' => 'delivered', 'payout' => 980],
            ['num' => '010', 'status' => DeliveryAssignment::STATUS_DELIVERED, 'driver_id' => $driverId, 'order_status' => 'delivered', 'payout' => 720],
            ['num' => '011', 'status' => DeliveryAssignment::STATUS_CANCELLED, 'driver_id' => $driverId, 'order_status' => 'cancelled', 'payout' => 600],
        ];

        foreach ($scenarios as $index => $scenario) {
            $vendor = $vendors[$index % count($vendors)];
            $customer = $customers[$index % count($customers)];
            $customerUser = Users::find($customer->user_id);
            $orderNumber = 'DEMO-ORD-' . $scenario['num'];
            $total = (float) $scenario['payout'] + 200;

            $order = Orders::updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'customer_id' => $customer->customer_id,
                    'vendor_id' => $vendor->vendor_id,
                    'subtotal' => $total - 50,
                    'tax_amount' => 50,
                    'gst_amount' => 50,
                    'shipping_amount' => 0,
                    'total_amount' => $total,
                    'payment_method' => 'online',
                    'payment_status' => 'paid',
                    'order_status' => $scenario['order_status'],
                    'shipping_address' => json_encode([
                        'contact_name' => $customerUser?->name ?? 'Customer',
                        'mobile' => $customerUser?->mobile,
                        'address_line' => $customer->customer_address,
                        'city' => 'Gurugram',
                        'state' => 'Haryana',
                    ]),
                ]
            );

            $assignedAt = in_array($scenario['status'], [
                DeliveryAssignment::STATUS_ASSIGNED,
                DeliveryAssignment::STATUS_PICKED_UP,
                DeliveryAssignment::STATUS_OUT_FOR_DELIVERY,
                DeliveryAssignment::STATUS_DELIVERED,
                DeliveryAssignment::STATUS_CANCELLED,
            ], true) ? now()->subHours(6 - $index) : null;

            $completedAt = $scenario['status'] === DeliveryAssignment::STATUS_DELIVERED
                ? now()->subDays($index % 5 + 1)
                : null;

            DeliveryAssignment::updateOrCreate(
                ['order_id' => $order->order_id],
                [
                    'driver_id' => $scenario['driver_id'],
                    'status' => $scenario['status'],
                    'payout_amount' => $scenario['payout'],
                    'pickup_address' => $vendor->address,
                    'delivery_address' => $customer->customer_address,
                    'store_name' => $vendor->business_name,
                    'store_location_summary' => 'Gurugram',
                    'assigned_at' => $assignedAt,
                    'completed_at' => $completedAt,
                ]
            );
        }
    }

    private function seedWalletAndTransactions(Users $driver): void
    {
        if (!Schema::hasTable('driver_wallets') || !Schema::hasTable('driver_transactions')) {
            return;
        }

        $driverId = (int) $driver->user_id;

        $wallet = DriverWallet::updateOrCreate(
            ['driver_id' => $driverId],
            ['balance' => 0, 'pending_balance' => 0, 'currency' => 'INR']
        );

        $delivered = DeliveryAssignment::where('driver_id', $driverId)
            ->where('status', DeliveryAssignment::STATUS_DELIVERED)
            ->with('order.customer.user')
            ->get();

        $balance = 0.0;
        foreach ($delivered as $assignment) {
            $ref = 'TXN-DEMO-CR-' . str_pad((string) $assignment->assignment_id, 4, '0', STR_PAD_LEFT);
            $amount = (float) $assignment->payout_amount;
            $balance += $amount;

            $customerName = $assignment->order?->customer?->user?->name ?? 'Customer';
            $orderRef = $assignment->order?->order_number ?? ('#OID' . $assignment->order_id);

            DriverTransaction::updateOrCreate(
                ['transaction_ref' => $ref],
                [
                    'driver_id' => $driverId,
                    'type' => DriverTransaction::TYPE_CREDIT,
                    'status' => DriverTransaction::STATUS_COMPLETED,
                    'amount' => $amount,
                    'balance_after' => $balance,
                    'title' => $customerName,
                    'subtitle' => $orderRef,
                    'order_id' => $assignment->order_id,
                    'assignment_id' => $assignment->assignment_id,
                    'created_at' => $assignment->completed_at ?? now()->subDays(2),
                    'updated_at' => $assignment->completed_at ?? now()->subDays(2),
                ]
            );
        }

        $withdrawal = null;
        if (Schema::hasTable('driver_withdrawals')) {
            $withdrawal = DriverWithdrawal::updateOrCreate(
                [
                    'driver_id' => $driverId,
                    'amount' => 500,
                    'status' => DriverWithdrawal::STATUS_PENDING,
                ],
                []
            );

            $balance -= 500;
            DriverTransaction::updateOrCreate(
                ['transaction_ref' => 'TXN-DEMO-WD-001'],
                [
                    'driver_id' => $driverId,
                    'type' => DriverTransaction::TYPE_DEBIT,
                    'status' => DriverTransaction::STATUS_PENDING,
                    'amount' => 500,
                    'balance_after' => max(0, $balance),
                    'title' => 'Withdrawal',
                    'subtitle' => 'Bank transfer',
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                ]
            );
        }

        $wallet->balance = max(0, $balance);
        $wallet->pending_balance = $withdrawal ? 500 : 0;
        $wallet->save();
    }

    private function seedNotifications(Users $driver): void
    {
        if (!Schema::hasTable('driver_notifications')) {
            return;
        }

        $driverId = (int) $driver->user_id;

        $items = [
            ['title' => 'Order Delivered', 'message' => 'Delivered to Shreya Shah', 'type' => 'order_delivered', 'hours_ago' => 2, 'read' => false],
            ['title' => 'New Delivery Assigned', 'message' => 'You accepted order DEMO-ORD-004', 'type' => 'new_delivery_assigned', 'hours_ago' => 5, 'read' => false],
            ['title' => 'Order Cancelled', 'message' => 'Order DEMO-ORD-011 was cancelled', 'type' => 'order_cancelled', 'hours_ago' => 26, 'read' => true],
            ['title' => 'Order Delivered', 'message' => 'Delivered to Rahul Verma', 'type' => 'order_delivered', 'hours_ago' => 30, 'read' => true],
            ['title' => 'New Delivery Assigned', 'message' => 'New delivery from Domino\'s Pizza', 'type' => 'new_delivery_assigned', 'hours_ago' => 48, 'read' => true],
        ];

        foreach ($items as $index => $item) {
            $createdAt = now()->subHours($item['hours_ago']);

            DriverNotification::updateOrCreate(
                [
                    'driver_id' => $driverId,
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'type' => $item['type'],
                ],
                [
                    'is_read' => $item['read'],
                    'read_at' => $item['read'] ? $createdAt->copy()->addHour() : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }
    }
}
