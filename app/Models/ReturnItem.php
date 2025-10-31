<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReturnRequest;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'return_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'returnID',
        'product_variant_id',
        'quantity',
    ];

    // Each return item belongs to a return
    public function return()
    {
        return $this->belongsTo(ReturnRequest::class, 'returnID', 'returnID');
    }        

    // Each return item belongs to a product variant
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }

    public function customer()
    {
        return $this->hasOneThrough(
            Customer::class,
            ReturnRequest::class,
            'returnID', 
            'customerID',    
            'returnID',      
            'customerID'     
        );
    }
}
