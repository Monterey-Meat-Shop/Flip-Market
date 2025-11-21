<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'gcash_account_name',
        'gcash_number',
        'gcash_qr',

        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_qr',
        
    ];
}
