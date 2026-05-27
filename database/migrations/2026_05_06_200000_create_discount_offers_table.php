<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Discount type: 'percentage' or 'fixed'
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount_value', 10, 2);   // e.g. 10.00 = 10% or ₹10

            // Scope: 'all', 'specific_products', 'specific_categories'
            $table->enum('apply_to', ['all', 'specific_products', 'specific_categories'])->default('all');
            $table->json('product_ids')->nullable();    // used when apply_to = specific_products
            $table->json('category_ids')->nullable();   // used when apply_to = specific_categories

            // Validity window
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            // Quantity-based conditions (applied per cart line)
            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();

            // Cart-amount-based conditions (applied on cart subtotal)
            $table->decimal('min_cart_amount', 10, 2)->nullable();
            $table->decimal('max_cart_amount', 10, 2)->nullable();

            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_offers');
    }
};
