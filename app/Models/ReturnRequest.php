<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'returns';
    protected $primaryKey = 'returnID';

    protected $fillable = [
        'orderID',
        'customerID',
        'return_reason',
        'other_reason',
        'product_image',
        'return_status',
        'returned_items',
        'admin_notes',
        'approved_at',
        'rejected_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'returned_items' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,   
            Order::class,       
            'orderID',          
            'orderID',          
            'orderID',          
            'orderID'           
        );
    }

    // Get the customer that owns the return request
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // Get reason label
    public function getReturnReasonLabelAttribute()
    {
        $reasons = [
            'not_delivered' => 'Product Not Delivered',
            'defective' => 'Defective or Damaged Product',
            'changed_mind' => 'Changed Mind/Not as Expected',
            'incorrect' => 'Incorrect Product Received',
            'other' => 'Other Reason',
        ];

        return $reasons[$this->return_reason] ?? $this->return_reason;
    }

    // Get status label
    public function getReturnStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
        ];

        return $statuses[$this->return_status] ?? $this->return_status;
    }

    // Get status color
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-green-100 text-green-800',
        ];

        return $colors[$this->return_status] ?? 'bg-gray-100 text-gray-800';
    }

    // Scope for pending returns
    public function scopePending($query)
    {
        return $query->where('return_status', 'pending');
    }

    //Scope for approved returns
    public function scopeApproved($query)
    {
        return $query->where('return_status', 'approved');
    }

    // Get the product names and colorways
    public function getProductNamesAttribute()
    {
        if (empty($this->returned_items) || !is_array($this->returned_items)) {
            return '-';
        }

            // Collect all order_itemIDs
            $itemIds = collect($this->returned_items)->pluck('order_itemID')->filter();
            if ($itemIds->isEmpty()) {
                return '-';
            }

            // Load order items with product + variant
            $orderItems = \App\Models\OrderItem::with(['product', 'productVariant'])
                ->whereIn('order_itemID', $itemIds)
                ->get();

                if ($orderItems->isEmpty()) {
                    return '-';
                }

                // Format the list of returned products
                $details = $orderItems->map(function ($item) use ($itemIds) {
                $productName = $item->product->name ?? 'Unknown Product';
                $color = $item->colorway ?? 'N/A';
                $size = $item->productVariant->size ?? 'N/A';

                // Find quantity from returned_items JSON
                $qty = collect($this->returned_items)
                    ->firstWhere('order_itemID', $item->order_itemID)['quantity'] ?? 1;

                return "• {$productName} ({$color} | Size: {$size}) × {$qty}";
            });

        return $details->implode('<br>');
    }

    // Add helper method to get items
    public function getReturnedItemsDetailsAttribute()
    {
        if (empty($this->returned_items)) {
            return collect();
        }

        // Collect IDs from your returned_items JSON
        $itemIds = collect($this->returned_items)
            ->pluck('orderItemID')
            ->map(fn($id) => (int) $id) // ensure integer
            ->toArray();

        // Fetch order items that match
        $orderItems = \App\Models\OrderItem::with('product', 'productVariant')
            ->whereIn('order_itemID', $itemIds)
            ->get();

        // Map return data with order item details
        return collect($this->returned_items)->map(function ($item) use ($orderItems) {
            $orderItem = $orderItems->firstWhere('order_itemID', (int) $item['orderItemID']);

            if (!$orderItem) {
                return [
                    'orderItem' => null,
                    'quantity' => $item['quantity'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            return [
                'orderItem' => $orderItem,
                'quantity' => $item['quantity'] ?? $orderItem->quantity ?? 0,
                'notes' => $item['notes'] ?? null,
            ];
        });
    }
}