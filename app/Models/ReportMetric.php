<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_date',
        'orders_count',
        'orders_total',
        'orders_last7',
        'payments_count',
        'payments_total',
        'meta',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'orders_total' => 'float',
        'payments_total' => 'float',
        'meta' => 'array',
    ];
}