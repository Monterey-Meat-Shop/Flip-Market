<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('discountID')->nullable()->after('product_variant_id');
            $table->foreign('discountID')->references('discountID')->on('discounts')->onDelete('set null');

            $table->decimal('original_price', 10, 2)->nullable()->after('unit_price');
            $table->string('discount_name')->nullable()->after('original_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['discountID', 'original_price', 'discount_name', 'discount_amount']);
        });
    }
};
