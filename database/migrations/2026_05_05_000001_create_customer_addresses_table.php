<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->bigIncrements('customer_address_id');
            $table->integer('customer_id');
            $table->string('contact_name', 120)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address_line');
            $table->string('landmark', 255)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('address_type', 50)->default('other');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'is_default'], 'customer_addresses_customer_default_idx');
        });

        DB::table('customers')
            ->leftJoin('users', 'customers.user_id', '=', 'users.user_id')
            ->whereNotNull('customers.customer_address')
            ->where('customers.customer_address', '!=', '')
            ->orderBy('customers.customer_id')
            ->select(
                'customers.customer_id',
                'customers.customer_address',
                'users.name as contact_name',
                'users.mobile'
            )
            ->get()
            ->each(function ($customer) {
                DB::table('customer_addresses')->insert([
                    'customer_id' => $customer->customer_id,
                    'contact_name' => $customer->contact_name,
                    'mobile' => $customer->mobile,
                    'address_line' => $customer->customer_address,
                    'address_type' => 'other',
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};