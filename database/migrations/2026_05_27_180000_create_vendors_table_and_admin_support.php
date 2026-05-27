<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id', true);
                $table->string('vendor_code', 20)->unique();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('owner_name', 100);
                $table->string('mobile', 15);
                $table->string('alternate_mobile', 15)->nullable();
                $table->string('email', 255);
                $table->date('dob')->nullable();
                $table->string('gender', 20)->nullable();
                $table->text('address')->nullable();
                $table->string('profile_image', 255)->nullable();
                $table->string('business_name', 150);
                $table->string('business_type', 50)->nullable();
                $table->string('business_email', 255)->nullable();
                $table->string('business_phone', 20)->nullable();
                $table->string('business_logo', 255)->nullable();
                $table->string('business_banner', 255)->nullable();
                $table->string('shop_image', 255)->nullable();
                $table->text('business_description')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('tax_name', 100)->nullable();
                $table->string('tax_number', 100)->nullable();
                $table->string('pan_number', 10)->nullable();
                $table->string('gst_number', 15)->nullable();
                $table->string('bank_account', 30)->nullable();
                $table->string('account_holder_name', 150)->nullable();
                $table->string('ifsc_code', 11)->nullable();
                $table->string('bank_name', 150)->nullable();
                $table->string('branch_name', 150)->nullable();
                $table->string('account_type', 50)->nullable();
                $table->string('upi_id', 100)->nullable();
                $table->decimal('commission_percent', 5, 2)->default(0);
                $table->decimal('wallet_balance', 12, 2)->default(0);
                $table->decimal('withdrawal_amount', 12, 2)->default(0);
                $table->decimal('refund_balance', 12, 2)->default(0);
                $table->string('aadhaar_card', 255)->nullable();
                $table->string('pan_card', 255)->nullable();
                $table->string('gst_file', 255)->nullable();
                $table->string('food_license_file', 255)->nullable();
                $table->string('bank_passbook_file', 255)->nullable();
                $table->string('address_proof_file', 255)->nullable();
                $table->string('national_identity_card_file', 255)->nullable();
                $table->enum('approval_status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
                $table->string('status', 5)->default('1');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('product_id')->index();
            });
        }

        if (Schema::hasTable('users_role')) {
            $exists = DB::table('users_role')->where('role_type', 3)->exists();
            if (!$exists) {
                DB::table('users_role')->insert([
                    'role_type' => 3,
                    'role' => 'Vendor',
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }

        Schema::dropIfExists('vendors');

        if (Schema::hasTable('users_role')) {
            DB::table('users_role')->where('role_type', 3)->delete();
        }
    }
};
