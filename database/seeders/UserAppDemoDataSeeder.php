<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserAppDemoDataSeeder extends Seeder
{
    private const DEMO_CUSTOMER_EMAIL = 'userapp.demo@moahaar.local';
    private const DEMO_CUSTOMER_MOBILE = '9876543210';
    private const DEMO_PASSWORD = 'Customer@123';

    public function run(): void
    {
        $this->seedStaticPagesAndStoreSettings();
        $vendorIds = $this->seedVendors();
        [$categoryId, $subCategoryId] = $this->seedCategories();
        $productIds = $this->seedProducts($vendorIds, $categoryId, $subCategoryId);
        $this->seedBanners();
        $this->seedDiscountOffers($categoryId, $productIds);

        $userId = $this->seedCustomerUser();
        $customerId = $this->seedCustomerProfile($userId);
        $addressIds = $this->seedCustomerAddresses($customerId);

        $this->seedCart($customerId, $productIds);
        $orderId = $this->seedOrders($customerId, $vendorIds, $productIds, $addressIds);
        $this->seedOrderTracking($orderId);
        $this->seedNotifications($customerId, $orderId);
        $this->seedTickets($userId);
        $this->seedProductReviews($customerId, $userId, $productIds);

        $this->command?->info('User app demo data seeded successfully.');
        $this->command?->info('Customer demo login (OTP flow): ' . self::DEMO_CUSTOMER_MOBILE);
        $this->command?->info('Fallback password (if needed): ' . self::DEMO_PASSWORD);
    }

    private function seedStaticPagesAndStoreSettings(): void
    {
        if (Schema::hasTable('static_pages')) {
            $pages = [
                ['slug' => 'privacy-policy', 'title' => 'Privacy Policy', 'content' => '<p>Mo Ahaar privacy policy demo content for user app.</p>'],
                ['slug' => 'terms-and-conditions', 'title' => 'Terms and Conditions', 'content' => '<p>Mo Ahaar terms and conditions demo content for user app.</p>'],
                ['slug' => 'faqs', 'title' => 'FAQs', 'content' => '<p><strong>How to place an order?</strong><br>Login, add items to cart, choose payment, and place order.</p>'],
                ['slug' => 'user-privacy-policy', 'title' => 'Privacy Policy', 'content' => '<p>Mo Ahaar privacy policy demo content for user app.</p>'],
                ['slug' => 'user-terms-and-conditions', 'title' => 'Terms and Conditions', 'content' => '<p>Mo Ahaar terms and conditions demo content for user app.</p>'],
                ['slug' => 'user-faqs', 'title' => 'FAQs', 'content' => '<p><strong>How to place an order?</strong><br>Login, add items to cart, choose payment, and place order.</p>'],
            ];
            foreach ($pages as $page) {
                $this->upsert('static_pages', ['slug' => $page['slug']], $page + ['status' => 1]);
            }
        }

        if (Schema::hasTable('store_settings')) {
            $existing = DB::table('store_settings')->first();
            $data = [
                'app_name' => 'Mo Ahaar',
                'support_email' => 'support@moahaar.com',
                'support_number' => '1800123456',
                'customer_home_sliders_enabled' => 1,
                'customer_home_offers_enabled' => 1,
                'customer_home_promotions_enabled' => 1,
                'customer_home_announcements_enabled' => 1,
                'customer_home_featured_products_enabled' => 1,
                'customer_registration_privacy_policy_enabled' => 1,
                'customer_registration_terms_enabled' => 1,
                'customer_registration_faq_enabled' => 1,
            ];

            $filtered = $this->filterColumns('store_settings', $data);
            if ($existing) {
                DB::table('store_settings')->where('id', $existing->id)->update($filtered);
            } else {
                DB::table('store_settings')->insert($filtered);
            }
        }
    }

    /**
     * @return array<int,int> vendor IDs
     */
    private function seedVendors(): array
    {
        if (!Schema::hasTable('vendors')) {
            return [];
        }

        $rows = [
            [
                'vendor_code' => 'UAPP-VEND-001',
                'business_name' => 'Domino\'s Pizza',
                'owner_name' => 'Rohit Mehra',
                'email' => 'dominos.userapp@moahaar.local',
                'mobile' => '9811100001',
                'address' => 'Medanta Hospital Road, Gurugram',
                'latitude' => 28.4245,
                'longitude' => 77.0401,
                'status' => '1',
                'approval_status' => 'approved',
            ],
            [
                'vendor_code' => 'UAPP-VEND-002',
                'business_name' => 'Chai Leela',
                'owner_name' => 'Ankit Jain',
                'email' => 'chaileela.userapp@moahaar.local',
                'mobile' => '9811100002',
                'address' => 'Vijay Nagar, Indore',
                'latitude' => 22.7532,
                'longitude' => 75.8937,
                'status' => '1',
                'approval_status' => 'approved',
            ],
            [
                'vendor_code' => 'UAPP-VEND-003',
                'business_name' => 'Haldiram Restaurant',
                'owner_name' => 'Vikas Sharma',
                'email' => 'haldiram.userapp@moahaar.local',
                'mobile' => '9811100003',
                'address' => 'Palasia Square, Indore',
                'latitude' => 22.7258,
                'longitude' => 75.8776,
                'status' => '1',
                'approval_status' => 'approved',
            ],
        ];

        $ids = [];
        foreach ($rows as $row) {
            $this->upsert('vendors', ['vendor_code' => $row['vendor_code']], $row);
            $vendorId = (int) DB::table('vendors')->where('vendor_code', $row['vendor_code'])->value('vendor_id');
            if ($vendorId > 0) {
                $ids[] = $vendorId;
            }
        }

        return $ids;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function seedCategories(): array
    {
        $categoryId = 0;
        $subCategoryId = 0;

        if (Schema::hasTable('product_categories')) {
            $this->upsert('product_categories', ['slug' => 'pizza'], [
                'category_name' => 'Pizza',
                'slug' => 'pizza',
                'category_description' => 'Pizza category for app demo',
                'status' => 1,
            ]);

            $categoryId = (int) DB::table('product_categories')->where('slug', 'pizza')->value('category_id');
        }

        if ($categoryId > 0 && Schema::hasTable('sub_categories')) {
            $this->upsert('sub_categories', ['sub_cat_slug' => 'veg-pizza'], [
                'category_id' => $categoryId,
                'sub_cat_name' => 'Veg Pizza',
                'sub_cat_slug' => 'veg-pizza',
                'sub_cat_description' => 'Veg pizza products',
                'status' => 1,
            ]);

            $subCategoryId = (int) DB::table('sub_categories')->where('sub_cat_slug', 'veg-pizza')->value('sub_category_id');
        }

        return [$categoryId, $subCategoryId];
    }

    /**
     * @param  array<int,int>  $vendorIds
     * @return array<int,int> product IDs
     */
    private function seedProducts(array $vendorIds, int $categoryId, int $subCategoryId): array
    {
        if (!Schema::hasTable('products') || empty($vendorIds)) {
            return [];
        }

        $rows = [
            [
                'sku' => 'UAPP-HON-PIZ-001',
                'product_name' => 'Honey Pizza',
                'short_description' => 'Carrot, avocado, peas, olives',
                'price' => 240,
                'sale_price' => 220,
                'discount' => 20,
                'stock' => 120,
                'featured' => 1,
                'target_user_type' => 'Retailer',
                'vendor_id' => $vendorIds[0],
            ],
            [
                'sku' => 'UAPP-CHO-TRU-002',
                'product_name' => 'Chocolate Truffle Ice Cream',
                'short_description' => 'Premium truffle dessert',
                'price' => 89,
                'sale_price' => null,
                'discount' => 0,
                'stock' => 180,
                'featured' => 1,
                'target_user_type' => 'Retailer',
                'vendor_id' => $vendorIds[0],
            ],
            [
                'sku' => 'UAPP-BUR-003',
                'product_name' => 'Classic Veg Burger',
                'short_description' => 'Crispy patty and fresh veggies',
                'price' => 240,
                'sale_price' => null,
                'discount' => 0,
                'stock' => 95,
                'featured' => 1,
                'target_user_type' => 'Retailer',
                'vendor_id' => $vendorIds[1] ?? $vendorIds[0],
            ],
        ];

        $ids = [];
        foreach ($rows as $row) {
            $payload = [
                'sku' => $row['sku'],
                'product_name' => $row['product_name'],
                'short_description' => $row['short_description'],
                'product_slug' => strtolower(str_replace(' ', '-', $row['sku'])),
                'category_id' => $categoryId > 0 ? $categoryId : null,
                'sub_category_id' => $subCategoryId > 0 ? $subCategoryId : null,
                'vendor_id' => $row['vendor_id'],
                'price' => $row['price'],
                'sale_price' => $row['sale_price'],
                'discount' => $row['discount'],
                'stock' => $row['stock'],
                'min_quantity' => 1,
                'status' => 1,
                'is_active_status' => 1,
                'featured' => $row['featured'],
                'sale_status' => $row['sale_price'] ? 1 : 0,
                'target_user_type' => $row['target_user_type'],
                'gst_percentage' => 18,
                'gst_calculation_type' => 'excluded',
                'tags' => 'veg,pizza,user-app',
            ];

            $this->upsert('products', ['sku' => $row['sku']], $payload);
            $id = (int) DB::table('products')->where('sku', $row['sku'])->value('product_id');
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function seedBanners(): void
    {
        if (!Schema::hasTable('banners')) {
            return;
        }

        $rows = [
            [
                'title' => 'Flat 20% Off On Pizza',
                'subtitle' => 'Limited period offer',
                'location' => 'Home Slider',
                'banner_image' => 'demo-banner-pizza.jpg',
                'button_text' => 'Order Now',
                'button_link' => '/customer-app/products/search?q=pizza',
                'banner_type' => 'offer',
                'status' => 1,
                'visible_from' => now()->subDays(2)->toDateString(),
                'visible_to' => now()->addDays(30)->toDateString(),
            ],
            [
                'title' => 'Free Delivery Above 299',
                'subtitle' => 'Save more with every order',
                'location' => 'Home Slider',
                'banner_image' => 'demo-banner-delivery.jpg',
                'button_text' => 'View Cart',
                'button_link' => '/customer-app/cart',
                'banner_type' => 'promotion',
                'status' => 1,
                'visible_from' => now()->subDays(2)->toDateString(),
                'visible_to' => now()->addDays(30)->toDateString(),
            ],
        ];

        foreach ($rows as $row) {
            $this->upsert('banners', ['title' => $row['title']], $row);
        }
    }

    /**
     * @param  array<int,int>  $productIds
     */
    private function seedDiscountOffers(int $categoryId, array $productIds): void
    {
        if (!Schema::hasTable('discount_offers')) {
            return;
        }

        $this->upsert('discount_offers', ['title' => 'TRIVAGO'], [
            'title' => 'TRIVAGO',
            'description' => 'User app promo code demo',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'apply_to' => 'all',
            'product_ids' => json_encode($productIds),
            'category_ids' => $categoryId > 0 ? json_encode([$categoryId]) : null,
            'min_cart_amount' => 200,
            'max_cart_amount' => null,
            'is_active' => 1,
            'valid_from' => now()->subDays(1)->toDateString(),
            'valid_until' => now()->addDays(45)->toDateString(),
        ]);
    }

    private function seedCustomerUser(): int
    {
        if (!Schema::hasTable('users')) {
            return 0;
        }

        $this->upsert('users', ['email' => self::DEMO_CUSTOMER_EMAIL], [
            'name' => 'Shreya Shah',
            'email' => self::DEMO_CUSTOMER_EMAIL,
            'mobile' => self::DEMO_CUSTOMER_MOBILE,
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role_type' => Users::CUSTOMER_APP_ROLE_TYPE,
            'user_type' => 'Retailer',
            'status' => 1,
            'approval_status' => 'approved',
            'preferred_language' => 'en',
            'login_otp' => '1234',
            'login_otp_expires_at' => now()->addMinutes(20),
        ]);

        return (int) DB::table('users')->where('email', self::DEMO_CUSTOMER_EMAIL)->value('user_id');
    }

    private function seedCustomerProfile(int $userId): int
    {
        if ($userId <= 0 || !Schema::hasTable('customers')) {
            return 0;
        }

        $this->upsert('customers', ['user_id' => $userId], [
            'user_id' => $userId,
            'dob' => '1998-02-14',
            'gender' => 'female',
            'customer_address' => 'G-14 1st Sabari Nagar, Sukhliya, Indore',
            'latitude' => 22.7196,
            'longitude' => 75.8577,
            'location_enabled' => 1,
            'location_updated_at' => now(),
            'cart_cooking_instructions' => 'Less spicy, no onion',
            'cart_promo_code' => 'TRIVAGO',
        ]);

        return (int) DB::table('customers')->where('user_id', $userId)->value('customer_id');
    }

    /**
     * @return array<int,int>
     */
    private function seedCustomerAddresses(int $customerId): array
    {
        if ($customerId <= 0 || !Schema::hasTable('customer_addresses')) {
            return [];
        }

        $rows = [
            [
                'customer_id' => $customerId,
                'contact_name' => 'Shreya Shah',
                'mobile' => '9517530286',
                'address_line' => 'G-14 1st',
                'landmark' => 'Sabari Nagar, Sukhliya',
                'city' => 'Indore',
                'state' => 'M.P',
                'country' => 'India',
                'pincode' => '452001',
                'address_type' => 'home',
                'is_default' => 1,
            ],
            [
                'customer_id' => $customerId,
                'contact_name' => 'Shreya Shah',
                'mobile' => '9517530286',
                'address_line' => 'ABC Colony',
                'landmark' => 'Near Lal Bagh',
                'city' => 'Indore',
                'state' => 'M.P',
                'country' => 'India',
                'pincode' => '452005',
                'address_type' => 'office',
                'is_default' => 0,
            ],
        ];

        $ids = [];
        foreach ($rows as $index => $row) {
            $this->upsert(
                'customer_addresses',
                ['customer_id' => $customerId, 'address_type' => $row['address_type'], 'address_line' => $row['address_line']],
                $row
            );
            $id = (int) DB::table('customer_addresses')
                ->where('customer_id', $customerId)
                ->where('address_type', $row['address_type'])
                ->where('address_line', $row['address_line'])
                ->value('customer_address_id');
            if ($id > 0) {
                $ids[] = $id;
                if ($index === 0 && Schema::hasColumn('customers', 'cart_selected_address_id')) {
                    DB::table('customers')->where('customer_id', $customerId)->update(['cart_selected_address_id' => $id]);
                }
            }
        }

        return $ids;
    }

    /**
     * @param  array<int,int>  $productIds
     */
    private function seedCart(int $customerId, array $productIds): void
    {
        if ($customerId <= 0 || !Schema::hasTable('cart_items') || empty($productIds)) {
            return;
        }

        $firstProductId = $productIds[0];
        $price = (float) DB::table('products')->where('product_id', $firstProductId)->value('price');
        $salePrice = DB::table('products')->where('product_id', $firstProductId)->value('sale_price');

        $this->upsert('cart_items', ['customer_id' => $customerId, 'product_id' => $firstProductId], [
            'customer_id' => $customerId,
            'product_id' => $firstProductId,
            'quantity' => 1,
            'unit_price' => $price > 0 ? $price : 240,
            'sale_price' => $salePrice,
        ]);
    }

    /**
     * @param  array<int,int>  $vendorIds
     * @param  array<int,int>  $productIds
     * @param  array<int,int>  $addressIds
     */
    private function seedOrders(int $customerId, array $vendorIds, array $productIds, array $addressIds): int
    {
        if ($customerId <= 0 || !Schema::hasTable('orders') || empty($productIds)) {
            return 0;
        }

        $vendorId = $vendorIds[0] ?? null;
        $addressId = $addressIds[0] ?? null;
        $shippingAddress = [
            'contact_name' => 'Shreya Shah',
            'mobile' => '9517530286',
            'address_line' => 'G-14 1st',
            'landmark' => 'Sabari Nagar, Sukhliya',
            'city' => 'Indore',
            'state' => 'M.P',
            'country' => 'India',
            'pincode' => '452001',
            'customer_address_id' => $addressId,
            'formatted_address' => 'G-14 1st, Sabari Nagar, Sukhliya, Indore, M.P, India, 452001',
        ];

        $orderNumber = 'UAPP-DEMO-001';
        $orderData = [
            'customer_id' => $customerId,
            'vendor_id' => $vendorId,
            'order_number' => $orderNumber,
            'subtotal' => 580,
            'tax_amount' => 18,
            'gst_amount' => 18,
            'shipping_amount' => 40,
            'total_amount' => 638,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'preparing',
            'shipping_address' => json_encode($shippingAddress),
            'notes' => 'Demo order for user app APIs',
        ];

        $this->upsert('orders', ['order_number' => $orderNumber], $orderData);
        $orderId = (int) DB::table('orders')->where('order_number', $orderNumber)->value('order_id');

        if ($orderId > 0 && Schema::hasTable('order_items')) {
            foreach (array_slice($productIds, 0, 2) as $index => $productId) {
                $product = DB::table('products')->where('product_id', $productId)->first();
                if (!$product) {
                    continue;
                }
                $qty = $index === 0 ? 2 : 1;
                $effective = (float) ($product->sale_price ?: $product->price);
                $lineTotal = $effective * $qty;

                $this->upsert('order_items', ['order_id' => $orderId, 'product_id' => $productId], [
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'product_name' => $product->product_name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_price' => $effective,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'gst_amount' => 0,
                    'gst_percentage' => 18,
                    'gst_calculation_type' => 'excluded',
                    'effective_price' => $effective,
                    'line_total' => $lineTotal,
                    'item_status' => 'preparing',
                ]);
            }
        }

        return $orderId;
    }

    private function seedOrderTracking(int $orderId): void
    {
        if ($orderId <= 0 || !Schema::hasTable('order_tracking')) {
            return;
        }

        $events = [
            ['status' => 'order_placed', 'description' => 'Order placed successfully', 'minutes' => 45],
            ['status' => 'confirmed', 'description' => 'Payment verified and order confirmed', 'minutes' => 35],
            ['status' => 'preparing', 'description' => 'Restaurant is preparing your food', 'minutes' => 10],
        ];

        foreach ($events as $event) {
            $this->upsert(
                'order_tracking',
                ['order_id' => $orderId, 'status' => $event['status']],
                [
                    'order_id' => $orderId,
                    'status' => $event['status'],
                    'location' => 'Indore',
                    'description' => $event['description'],
                    'tracked_at' => now()->subMinutes($event['minutes']),
                ]
            );
        }
    }

    private function seedNotifications(int $customerId, int $orderId): void
    {
        if ($customerId <= 0 || !Schema::hasTable('customer_notifications')) {
            return;
        }

        $rows = [
            [
                'customer_id' => $customerId,
                'source_type' => 'order_update',
                'source_id' => $orderId,
                'order_id' => $orderId,
                'title' => 'Order is being prepared',
                'message' => 'Your order UAPP-DEMO-001 is currently preparing.',
                'meta' => json_encode(['order_status' => 'preparing']),
                'is_read' => 0,
            ],
            [
                'customer_id' => $customerId,
                'source_type' => 'offer',
                'source_id' => null,
                'order_id' => null,
                'title' => 'Promo code available',
                'message' => 'Use TRIVAGO to get instant discount.',
                'meta' => json_encode(['promo_code' => 'TRIVAGO']),
                'is_read' => 1,
            ],
        ];

        foreach ($rows as $row) {
            $this->upsert(
                'customer_notifications',
                ['customer_id' => $row['customer_id'], 'title' => $row['title']],
                $row
            );
        }
    }

    private function seedTickets(int $userId): void
    {
        if ($userId <= 0 || !Schema::hasTable('tickets')) {
            return;
        }

        $adminId = null;
        if (Schema::hasTable('users')) {
            $this->upsert('users', ['email' => 'userapp.support@moahaar.local'], [
                'name' => 'User App Support',
                'email' => 'userapp.support@moahaar.local',
                'mobile' => '9000000005',
                'password' => Hash::make('password'),
                'role_type' => 1,
                'status' => 1,
            ]);
            $adminId = DB::table('users')->where('email', 'userapp.support@moahaar.local')->value('user_id');
        }

        $this->upsert('tickets', ['user_id' => $userId, 'subject' => 'Delivery took too long'], [
            'user_id' => $userId,
            'type' => 'order',
            'subject' => 'Delivery took too long',
            'description' => 'Order was delayed by 30 minutes.',
            'status' => 'in_progress',
            'priority' => 'medium',
            'assigned_to' => $adminId,
        ]);
    }

    /**
     * @param  array<int,int>  $productIds
     */
    private function seedProductReviews(int $customerId, int $userId, array $productIds): void
    {
        if ($customerId <= 0 || empty($productIds) || !Schema::hasTable('product_reviews')) {
            return;
        }

        foreach (array_slice($productIds, 0, 2) as $index => $productId) {
            $this->upsert(
                'product_reviews',
                ['customer_id' => $customerId, 'product_id' => $productId],
                [
                    'customer_id' => $customerId,
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'rating' => $index === 0 ? 5 : 4,
                    'review' => $index === 0 ? 'Excellent taste and fast delivery.' : 'Good quality product.',
                    'status' => 1,
                ]
            );
        }
    }

    /**
     * Upsert with column filtering + timestamps safety.
     *
     * @param  array<string,mixed>  $match
     * @param  array<string,mixed>  $data
     */
    private function upsert(string $table, array $match, array $data): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $filteredMatch = $this->filterColumns($table, $match);
        $filteredData = $this->filterColumns($table, $data);

        if (empty($filteredMatch)) {
            return;
        }

        if (Schema::hasColumn($table, 'updated_at') && !array_key_exists('updated_at', $filteredData)) {
            $filteredData['updated_at'] = now();
        }
        if (Schema::hasColumn($table, 'created_at') && !array_key_exists('created_at', $filteredData)) {
            $filteredData['created_at'] = now();
        }

        DB::table($table)->updateOrInsert($filteredMatch, $filteredData);
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    private function filterColumns(string $table, array $data): array
    {
        $out = [];
        foreach ($data as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $out[$column] = $value;
            }
        }

        return $out;
    }
}

