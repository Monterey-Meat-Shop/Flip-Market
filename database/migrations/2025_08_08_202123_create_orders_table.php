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
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('orderID');

            // Foreign keys
            $table->unsignedBigInteger('customerID');
            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');

            $table->unsignedInteger('discountID')->nullable();
            $table->foreign('discountID')->references('discountID')->on('discounts')->onDelete('set null');

            $table->datetime('order_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->enum('order_status', ['pending', 'processing', 'completed', 'cancelled', 'pre-order'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'verified'])->default('unpaid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};