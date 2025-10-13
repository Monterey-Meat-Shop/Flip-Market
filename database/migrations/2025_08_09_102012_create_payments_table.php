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
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('paymentID');

            // Match orders table PK
            $table->unsignedBigInteger('orderID');
            $table->foreign('orderID')
                  ->references('orderID')
                  ->on('orders')
                  ->onDelete('cascade');

            // Payment method (FK to payment_methodS table - PLURAL)
            $table->unsignedInteger('payment_methodID');
            $table->foreign('payment_methodID')
                  ->references('payment_methodID')
                  ->on('payment_methods');

            $table->decimal('amount', 8, 2)->default(0);
            $table->string('reference_number')->nullable();
            $table->string('screenshot_path')->nullable();

            $table->enum('status', ['unpaid', 'verified', 'completed', 'failed'])->default('unpaid');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
