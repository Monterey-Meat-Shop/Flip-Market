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

            // FIX: This must be an unsignedBigInteger to match the primary key on the 'orders' table.
            $table->unsignedBigInteger('orderID');
            $table->foreign('orderID')->references('orderID')->on('orders')->onDelete('cascade');

            $table->unsignedInteger('payment_methodID');
            $table->foreign('payment_methodID')->references('payment_methodID')->on('payment_method');

            $table->decimal('amount', 8, 2);
            $table->string('reference_number')->nullable();
            $table->string('screenshot_url')->nullable();
            $table->string('status');
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
