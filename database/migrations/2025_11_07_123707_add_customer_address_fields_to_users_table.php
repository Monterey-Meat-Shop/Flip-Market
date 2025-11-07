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
        Schema::table('users', function (Blueprint $table) {
            // Add only if they don't already exist
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'address_line_1')) {
                $table->string('address_line_1', 255)->nullable()->after('postal_code');
            }

            if (!Schema::hasColumn('users', 'address_line_2')) {
                $table->string('address_line_2', 255)->nullable()->after('address_line_1');
            }

            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('address_line_2');
            }

            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province', 100)->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'postal_code')) {
                $table->dropColumn('postal_code');
            }
            if (Schema::hasColumn('users', 'address_line_1')) {
                $table->dropColumn('address_line_1');
            }
            if (Schema::hasColumn('users', 'address_line_2')) {
                $table->dropColumn('address_line_2');
            }
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('users', 'province')) {
                $table->dropColumn('province');
            }
        });
    }
};
