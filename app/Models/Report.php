<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Report extends Model
{
    use HasFactory;

    // ensure 'title' is mass assignable (add other columns your app needs)
    protected $fillable = [
        'title',
        'description',
        'status',
        'report_date',
        'attachment',
        'is_visible',
    ];

    protected static function booted(): void
    {
        $clear = function () {
            $keys = [
                'reportstats:reports_count',
                // orders/payments keys used by the widget
                'reportstats:orders_count',
                'reportstats:orders_total',
                'reportstats:payments_count',
                'reportstats:payments_total',
                'reportstats:payments_pending',
                'reportstats:payments_failed',
                'reportstats:payments_refunded',
                // columns cache
                'reportstats:orders_amount_column',
                'reportstats:payments_amount_column',
                'reportstats:payments_total_rows',
                'reportstats:payments_total_excluding_pending',
            ];
            foreach ($keys as $k) {
                Cache::forget($k);
            }
        };

        static::saved($clear);
        static::deleted($clear);
    }
}
