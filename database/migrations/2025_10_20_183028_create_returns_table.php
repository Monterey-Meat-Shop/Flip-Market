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
        Schema::create('returns', function (Blueprint $table) {
            $table->id('returnID');
            
            // Foreign keys
            $table->unsignedBigInteger('orderID');
            $table->foreign('orderID')->references('orderID')->on('orders')->onDelete('cascade');
            
            $table->unsignedBigInteger('customerID');
            $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
            
            // Return details
            $table->enum('return_reason', [
                'not_delivered',
                'defective',
                //'changed_mind',
                'incorrect',
                'other'
            ]);
            $table->text('other_reason')->nullable();
            $table->string('product_image');
            
            // Return status and processing
            $table->enum('return_status', [
                'pending',
                'approved',
                'rejected',
                'completed',
                'refunded'
            ])->default('pending');
            
            $table->text('admin_notes')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('rejected_at')->nullable();
            $table->datetime('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
