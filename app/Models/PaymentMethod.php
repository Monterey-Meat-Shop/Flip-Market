<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_method';

    protected $primaryKey = 'payment_methodID';

    protected $fillable = [
        'method_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_methodID');
    }

    protected static function booted()
    {
        static::deleting(function (PaymentMethod $payment_method) {
            if (! $payment_method->isForceDeleting()) {
                $payment_method->is_active = false;
                $payment_method->saveQuietly();
            }
        });
    }
    
}
