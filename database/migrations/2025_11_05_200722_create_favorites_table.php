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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customerID');
            $table->unsignedBigInteger('productID');

            $table->timestamps();

            $table->foreign('customerID')
                  ->references('customerID')->on('customers')
                  ->onDelete('cascade');

            $table->foreign('productID')
                  ->references('productID')->on('products')
                  ->onDelete('cascade');

            $table->unique(['customerID', 'productID']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
