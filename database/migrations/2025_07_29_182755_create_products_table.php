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
            $table->id('ProductID');

            //Foreign Keys
            $table->foreignId('CategoryID')
                  ->constrained('categories', 'CategoryID')
                  ->onDelete('cascade');

             $table->foreignId('BrandID')
                   ->constrained('brands', 'BrandID')
                   ->onDelete('cascade');

            $table->string('Name');
            $table->string('Slug')->unique();
            $table->text('Description')->nullable();
            $table->decimal('Price', 10, 2);
            $table->json('image_url')->nullable();
            $table->integer('Stock_Quantity');
            $table->json('Size')->nullable();
            $table->string('Colorway')->nullable();

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
