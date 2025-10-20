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
        Schema::create('discount_product', function (Blueprint $table) {
            $table->unsignedInteger('discount_id');
            $table->foreign('discount_id')->references('discountID')->on('discounts')->onDelete('cascade');

            // FIX: This must be unsignedBigInteger to match products.productID
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('productID')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_product');
    }
};
