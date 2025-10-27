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
            $table->bigIncrements('orderID');

            // Foreign keys
            $table->unsignedBigInteger('customerID');
            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');

            $table->unsignedInteger('discountID')->nullable();
            $table->foreign('discountID')->references('discountID')->on('discounts')->onDelete('set null');

            $table->datetime('order_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('final_amount', 10, 2);
            $table->enum('order_status', [
                'pending', 
                'processing', 
                'completed', 
                'cancelled', 
                'pre-order',
                'return_requested',
                'returned',
                'failed'
            ])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'verified'])->default('unpaid');

            $table->boolean('stock_deducted')->default(false); //added

            $table->boolean('is_returnable')->default(true);  // NEW: Can this order be returned?
            $table->datetime('return_deadline')->nullable();  // NEW: Last date for returns (e.g., 7-30 days after delivery)
            
            $table->string('payment_method')->nullable();

            $table->string('address_choice')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();

            $table->timestamps();
            $table->softDeletes();
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
