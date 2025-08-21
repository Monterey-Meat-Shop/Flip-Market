<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportMetricsTable extends Migration
{
    public function up(): void
    {
        Schema::create('report_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->index();
            $table->unsignedBigInteger('orders_count')->default(0);
            $table->decimal('orders_total', 20, 2)->default(0);
            $table->unsignedBigInteger('orders_last7')->default(0);
            $table->unsignedBigInteger('payments_count')->default(0)->nullable();
            $table->decimal('payments_total', 20, 2)->default(0)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('metric_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_metrics');
    }
}