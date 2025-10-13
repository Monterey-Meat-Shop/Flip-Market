<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'customerID';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customerID', 'customerID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'customerID', 'customerID');
    }
     
    public function cartItems()
    {
       return $this->hasMany(CartItem::class, 'customerID', 'customerID');
    }
}
