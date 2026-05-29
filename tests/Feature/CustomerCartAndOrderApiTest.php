<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Customers;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerCartAndOrderApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildCommerceTables();
    }

    public function test_customer_can_add_update_and_remove_cart_items(): void
    {
        $user = $this->createCustomerUser();
        $customer = Customers::where('user_id', '=', $user->user_id)->firstOrFail();
        $product = $this->createProduct(['vendor_id' => 101]);

        $this->actingAs($user, 'web');

        $addResponse = $this->postJson('/api/customer-app/cart/add', [
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);

        $addResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.item.product_id', $product->product_id)
            ->assertJsonPath('data.item.quantity', 1)
            ->assertJsonPath('data.cart.items_count', 1);

        $cartItemId = (int) $addResponse->json('data.item.cart_item_id');

        $this->assertDatabaseHas('cart_items', [
            'cart_item_id' => $cartItemId,
            'customer_id' => $customer->customer_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
        ]);

        $updateResponse = $this->postJson('/api/customer-app/cart/update', [
            'cart_item_id' => $cartItemId,
            'quantity' => 3,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.item.quantity', 3)
            ->assertJsonPath('data.cart.items.0.quantity', 3);

        $removeResponse = $this->postJson('/api/customer-app/cart/remove', [
            'cart_item_id' => $cartItemId,
        ]);

        $removeResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.cart.items_count', 0);

        $this->assertDatabaseMissing('cart_items', [
            'cart_item_id' => $cartItemId,
        ]);
    }

    public function test_checkout_rejects_mixed_vendor_cart_with_explicit_reason(): void
    {
        $user = $this->createCustomerUser();
        $customer = Customers::where('user_id', '=', $user->user_id)->firstOrFail();
        $firstProduct = $this->createProduct(['vendor_id' => 101]);
        $secondProduct = $this->createProduct(['vendor_id' => 202, 'sku' => 'SKU-SECOND']);

        CartItem::create([
            'customer_id' => $customer->customer_id,
            'product_id' => $firstProduct->product_id,
            'quantity' => 1,
            'unit_price' => $firstProduct->price,
            'sale_price' => null,
        ]);

        CartItem::create([
            'customer_id' => $customer->customer_id,
            'product_id' => $secondProduct->product_id,
            'quantity' => 1,
            'unit_price' => $secondProduct->price,
            'sale_price' => null,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->postJson('/api/customer-app/checkout/place-order', [
            'shipping_address' => 'Test shipping address',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('data.reason', 'mixed_vendor_cart');

        $this->assertSame(0, DB::table('orders')->count());
    }

    public function test_checkout_rejects_unavailable_cart_item_with_explicit_reason(): void
    {
        $user = $this->createCustomerUser();
        $customer = Customers::where('user_id', '=', $user->user_id)->firstOrFail();
        $product = $this->createProduct([
            'vendor_id' => 101,
            'is_active_status' => 0,
        ]);

        CartItem::create([
            'customer_id' => $customer->customer_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'sale_price' => null,
        ]);

        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/customer-app/checkout/summary');

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('data.reason', 'unavailable_product')
            ->assertJsonPath('data.product_id', $product->product_id);
    }

    public function test_customer_can_place_cod_order_and_see_it_in_history(): void
    {
        $user = $this->createCustomerUser();
        $customer = Customers::where('user_id', '=', $user->user_id)->firstOrFail();
        $product = $this->createProduct([
            'vendor_id' => 101,
            'price' => 250.00,
        ]);

        CartItem::create([
            'customer_id' => $customer->customer_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'unit_price' => $product->price,
            'sale_price' => null,
        ]);

        $this->actingAs($user, 'web');

        $placeOrderResponse = $this->postJson('/api/customer-app/checkout/place-order', [
            'shipping_address' => 'Warehouse Road, Ahmedabad',
            'payment_method' => 'cod',
            'notes' => 'Handle carefully',
        ]);

        $placeOrderResponse->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.payment_method', 'cod')
            ->assertJsonPath('data.order_status', 'pending')
            ->assertJsonPath('data.items_ordered', 1);

        $orderId = (int) $placeOrderResponse->json('data.order_id');

        $this->assertDatabaseHas('orders', [
            'order_id' => $orderId,
            'customer_id' => $customer->customer_id,
            'vendor_id' => 101,
            'payment_method' => 'cod',
            'order_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'item_status' => 'pending',
        ]);

        $this->assertDatabaseHas('customer_notifications', [
            'customer_id' => $customer->customer_id,
            'order_id' => $orderId,
            'source_type' => 'order_update',
        ]);

        $this->assertDatabaseCount('cart_items', 0);

        $historyResponse = $this->getJson('/api/customer-app/orders');

        $historyResponse->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.orders.0.order_id', $orderId)
            ->assertJsonPath('data.orders.0.can_cancel', true);
    }

    private function rebuildCommerceTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['customer_notifications', 'order_items', 'orders', 'cart_items', 'products', 'customers', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('mobile')->nullable();
            $table->string('password')->nullable();
            $table->integer('role_type')->default(2);
            $table->string('user_type')->nullable();
            $table->integer('status')->default(1);
            $table->string('approval_status')->default('approved');
            $table->timestamp('gst_verified_at')->nullable();
            $table->string('preferred_language')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->increments('customer_id');
            $table->unsignedInteger('user_id');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('customer_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_enabled')->default(false);
            $table->timestamp('location_updated_at')->nullable();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->unsignedInteger('vendor_id')->nullable();
            $table->string('product_name');
            $table->string('short_description')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('sub_category_id')->nullable();
            $table->unsignedInteger('sub_sub_category_id')->nullable();
            $table->string('product_slug')->nullable();
            $table->string('product_type')->nullable();
            $table->string('store_name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('product_image')->nullable();
            $table->string('size_chart_image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->integer('sale_status')->default(0);
            $table->timestamp('sale_start_date')->nullable();
            $table->timestamp('sale_end_date')->nullable();
            $table->string('sku')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->string('stock_status')->nullable();
            $table->text('tags')->nullable();
            $table->integer('random_related_product')->default(0);
            $table->text('related_product_ids')->nullable();
            $table->text('cross_sell_product_ids')->nullable();
            $table->text('attribute_ids')->nullable();
            $table->string('video')->nullable();
            $table->integer('free_shipping')->default(0);
            $table->string('tax_name')->nullable();
            $table->string('estimated_delivery_text')->nullable();
            $table->string('return_policy_text')->nullable();
            $table->integer('featured')->default(0);
            $table->integer('safe_checkout')->default(0);
            $table->integer('secure_checkout')->default(0);
            $table->integer('social_share')->default(0);
            $table->integer('encourage_order')->default(0);
            $table->integer('encourage_view')->default(0);
            $table->integer('trending')->default(0);
            $table->integer('is_returnable')->default(0);
            $table->integer('is_active_status')->default(1);
            $table->integer('status')->default(1);
            $table->string('target_user_type')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('cart_item_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('vendor_id')->nullable();
            $table->string('order_number');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('order_status');
            $table->string('shipping_address');
            $table->text('notes')->nullable();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->string('item_status');
            $table->timestamps();
        });

        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->increments('notification_id');
            $table->unsignedInteger('customer_id');
            $table->string('source_type');
            $table->unsignedInteger('source_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->json('meta')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    private function createCustomerUser(array $overrides = []): Users
    {
        $user = Users::create(array_merge([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'mobile' => '9999999999',
            'role_type' => Users::CUSTOMER_APP_ROLE_TYPE,
            'status' => 1,
            'approval_status' => 'approved',
            'password' => bcrypt('password'),
        ], $overrides));

        Customers::create([
            'user_id' => $user->user_id,
            'customer_address' => 'Default customer address',
        ]);

        return $user;
    }

    private function createProduct(array $overrides = []): Product
    {
        static $sequence = 1;

        $product = Product::create(array_merge([
            'vendor_id' => 101,
            'product_name' => 'Product ' . $sequence,
            'short_description' => 'Short description',
            'product_slug' => 'product-' . $sequence,
            'product_type' => 'simple',
            'price' => 100.00,
            'sale_price' => null,
            'sku' => 'SKU-' . $sequence,
            'status' => 1,
            'is_active_status' => 1,
        ], $overrides));

        $sequence++;

        return $product;
    }
}