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
        Schema::create('shipping', function (Blueprint $table) {
            $table->id('shippingID');
            
            //foreign key
            $table->unsignedBigInteger('orderID');
            $table->foreign('orderID')->references('orderID')->on('orders')->onDelete('cascade');
            
            $table->string('shipping_method'); //Lalamove, JNT, 
            
            // Corrected enum and default value
            $table->enum('shipping_status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping');
    }
};
