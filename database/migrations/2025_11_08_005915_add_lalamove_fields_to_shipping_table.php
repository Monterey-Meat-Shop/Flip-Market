<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping', function (Blueprint $table) {
            $table->string('lalamove_booking_option')
                ->nullable()
                ->after('shipping_method'); // 'customer' or 'store'

            $table->string('lalamove_tracking')
                ->nullable()
                ->after('lalamove_booking_option'); // tracking number or URL (optional)
        });
    }

    public function down(): void
    {
        Schema::table('shipping', function (Blueprint $table) {
            $table->dropColumn(['lalamove_booking_option', 'lalamove_tracking']);
        });
    }
};
