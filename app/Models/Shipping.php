<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends Model
{
    use HasFactory;

    protected $table = 'shipping';

    protected $primaryKey = 'shippingID';

    protected $fillable = [
        'orderID',
        'shipping_method',
        'shipping_status',
    ];

    protected $attributes = [
        'shipping_status' => 'Pending',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }
}
