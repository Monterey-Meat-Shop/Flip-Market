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
        Schema::create('stock_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'productID');
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants');
            $table->integer('stock_quantity')->default(0);
            $table->integer('stock_quantity_received')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_order_items');
    }
};
