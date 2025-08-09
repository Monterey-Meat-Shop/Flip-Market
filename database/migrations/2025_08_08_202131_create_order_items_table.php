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
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('order_itemID');

            // foreign keys
            // FIX: This needs to be an unsignedInteger to match the primary key on the 'orders' table.
            $table->unsignedInteger('orderID');
            $table->foreign('orderID')->references('orderID')->on('orders')->onDelete('cascade');

            // Assumes 'products' table uses bigIncrements() or id() for its primary key.
            $table->unsignedBigInteger('productID');
            $table->foreign('productID')->references('productID')->on('products')->onDelete('cascade');

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            //$table->decimal('sub_total', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};