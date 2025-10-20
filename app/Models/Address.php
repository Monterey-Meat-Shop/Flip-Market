<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';

    protected $primaryKey = 'addressID';

    protected $fillable = [
        'customerID',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
