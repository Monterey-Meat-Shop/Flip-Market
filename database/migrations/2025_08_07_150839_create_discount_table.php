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
        Schema::create('discounts', function (Blueprint $table) {
            $table->increments('discountID');

            //Foreign Keys
            // $table->foreignId('productID')
            //       ->constrained('products', 'productID')
            //       ->onDelete('cascade');

            // $table->foreignId('orderID')
            //       ->constrained('orders', 'orderID')
            //       ->onDelete('cascade');

            $table->string('name');
            $table->string('discount_type');
            $table->decimal('discount_value', 10, 2);
            $table->boolean('is_active')->default(true);
            //$table->string('applies_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
