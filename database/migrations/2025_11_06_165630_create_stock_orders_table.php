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
        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
    
            $table->string('supplier_name');
            $table->string('purchase_order_number')->nullable();
    
            $table->integer('received_quantity')->default(0);
    
            $table->date('estimated_delivery_date');
            $table->date('actual_delivery_date')->nullable();
    
            $table->enum('status', ['pending', 'partial', 'completed', 'cancelled'])->default('pending');
    
            $table->text('notes')->nullable();
    
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('accepted_by')->nullable();
    
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('status');
            $table->index('estimated_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_orders');
    }
};
