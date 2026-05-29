<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\CustomerNotification;
use App\Models\Customers;
use App\Models\Users;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerNotificationRegistrationFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMinimalSchema();
    }

    public function test_customer_only_sees_notifications_after_registration(): void
    {
        $registeredAt = Carbon::parse('2026-05-28 12:00:00');

        $user = Users::create([
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'mobile' => '9876543210',
            'role_type' => Users::CUSTOMER_APP_ROLE_TYPE,
            'status' => 1,
            'approval_status' => 'approved',
            'password' => bcrypt('password'),
        ]);
        Users::where('user_id', $user->user_id)->update([
            'created_at' => $registeredAt,
            'updated_at' => $registeredAt,
        ]);
        $user->refresh();

        $customer = Customers::create([
            'user_id' => $user->user_id,
            'customer_address' => 'Test address',
        ]);

        CustomerNotification::create([
            'customer_id' => $customer->customer_id,
            'source_type' => 'promotional_offer',
            'source_id' => 1,
            'title' => 'Old Offer',
            'message' => 'Should be hidden',
            'is_read' => false,
            'created_at' => $registeredAt->copy()->subDay(),
            'updated_at' => $registeredAt->copy()->subDay(),
        ]);

        CustomerNotification::create([
            'customer_id' => $customer->customer_id,
            'source_type' => 'promotional_offer',
            'source_id' => 2,
            'title' => 'New Offer',
            'message' => 'Should be visible',
            'is_read' => false,
            'created_at' => $registeredAt->copy()->addHour(),
            'updated_at' => $registeredAt->copy()->addHour(),
        ]);

        AdminNotification::query()->create([
            'target_type' => 'users',
            'recipient_scope' => 'all',
            'recipient_id' => null,
            'recipient_name' => 'All Users',
            'title' => 'Pre-signup Broadcast',
            'message' => 'Old admin broadcast',
        ]);
        AdminNotification::query()
            ->where('title', 'Pre-signup Broadcast')
            ->update([
                'created_at' => $registeredAt->copy()->subDays(2),
                'updated_at' => $registeredAt->copy()->subDays(2),
            ]);

        $customer = Customers::where('user_id', $user->user_id)->firstOrFail();
        $this->assertNotNull($customer->registeredAt(), 'Registration timestamp missing');
        $this->assertTrue(
            $customer->registeredAt()->equalTo($registeredAt),
            'Expected registration at ' . $registeredAt . ' got ' . $customer->registeredAt()
        );

        $this->actingAs($user, 'web');

        $response = $this->getJson('/api/customer-app/notifications');

        $response->assertOk()
            ->assertJsonPath('status', true);

        $titles = collect($response->json('data.notifications'))->pluck('title')->all();

        $this->assertCount(1, $titles, 'Visible notifications: ' . json_encode($titles));
        $this->assertContains('New Offer', $titles);
        $this->assertNotContains('Old Offer', $titles);
        $this->assertNotContains('Pre-signup Broadcast', $titles);
        $this->assertSame(1, (int) $response->json('data.unread_count'));
    }

    private function createMinimalSchema(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (['customer_notifications', 'admin_notifications', 'order_trackings', 'orders', 'customers', 'users'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->unsignedTinyInteger('role_type')->default(2);
            $table->string('status')->default('1');
            $table->string('approval_status')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->increments('customer_id');
            $table->unsignedInteger('user_id');
            $table->string('customer_address')->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('customer_id');
            $table->string('order_number')->nullable();
            $table->timestamps();
        });

        Schema::create('order_trackings', function (Blueprint $table) {
            $table->increments('tracking_id');
            $table->unsignedInteger('order_id');
            $table->string('status')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('tracked_at')->nullable();
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

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('target_type');
            $table->string('recipient_scope');
            $table->unsignedInteger('recipient_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('title');
            $table->text('message');
            $table->unsignedInteger('sent_by')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }
}
