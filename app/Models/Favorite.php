<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'customerID',
        'productID',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID');
    }
}
