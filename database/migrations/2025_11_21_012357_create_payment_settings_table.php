<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();

            // GCash fields
            $table->string('gcash_account_name')->nullable();
            $table->string('gcash_number')->nullable();
            $table->string('gcash_qr')->nullable();

            // Bank Transfer fields
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_qr')->nullable();

            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
