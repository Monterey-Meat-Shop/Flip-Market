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
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();

            // Connect to returns table
            $table->foreignId('returnID')
                ->constrained('returns', 'returnID')
                ->cascadeOnDelete();

            // Connect to product_variants table
            $table->foreignId('product_variant_id')
                ->constrained('product_variants', 'id')
                ->cascadeOnDelete();

            // Quantity being returned
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
