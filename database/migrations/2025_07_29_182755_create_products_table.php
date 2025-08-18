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
        Schema::create('products', function (Blueprint $table) {
            $table->id('productID');

            //Foreign Keys
            $table->foreignId('categoryID')
                  ->constrained('categories', 'categoryID')
                  ->onDelete('cascade');

            $table->foreignId('brandID')
                  ->constrained('brands', 'brandID')
                  ->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('image_url')->nullable();

            // Use an enum for a more comprehensive status
            $table->enum('status', ['in_stock', 'pre_order', 'low_stock', 'out_of_stock'])->default('in_stock');

            $table->integer('stock_quantity');
            $table->json('size')->nullable();
            $table->string('colorway')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
