<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToReportsTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('status')->default('draft');
                $table->date('report_date')->nullable();
                $table->string('attachment')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (! Schema::hasColumn('reports', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('reports', 'status')) {
                $table->string('status')->default('draft')->after('description');
            }
            if (! Schema::hasColumn('reports', 'report_date')) {
                $table->date('report_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('reports', 'attachment')) {
                $table->string('attachment')->nullable()->after('report_date');
            }
            if (! Schema::hasColumn('reports', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('attachment');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reports')) {
            return;
        }

        Schema::table('reports', function (Blueprint $table) {
            foreach (['is_visible','attachment','report_date','status','description','title'] as $col) {
                if (Schema::hasColumn('reports', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
