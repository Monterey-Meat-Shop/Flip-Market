<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Notifications\Notification;

class StockOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_orders';

    protected $fillable = [
        'supplier_name',
        'purchase_order_number',
        'estimated_delivery_date',
        'notes',
        'received_quantity',
        'status',
        'created_by',
        'actual_delivery_date',
    ];

    // Relationships
    public function stockItems()
    {
        return $this->hasMany(StockOrderItem::class, 'stock_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function firstItem()
    {
        return $this->stockItems()->with(['product', 'productVariant'])->first();
    }

    public function productName(): string
    {
        return $this->firstItem()?->product?->name ?? 'N/A';
    }

    public function variantSize(): string
    {
        return $this->firstItem()?->productVariant?->size ?? 'N/A';
    }

    public function variantColor(): string
    {
        return $this->firstItem()?->productVariant?->colorway ?? 'N/A';
    }

    // Accessor for pending quantity
    public function getPendingQuantityAttribute(): int
    {
        $totals = $this->stockItems()
            ->selectRaw('SUM(stock_quantity) as total_ordered, SUM(stock_quantity_received) as total_received')
            ->first();

        return ($totals->total_ordered ?? 0) - ($totals->total_received ?? 0);
    }

    // Check if order is overdue
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->estimated_delivery_date < now();
    }

    // Check if delivery is approaching (within 3 days)
    public function isDeliveryApproaching(): bool
    {
        return $this->status !== 'completed' &&
               $this->estimated_delivery_date >= now() &&
               $this->estimated_delivery_date <= now()->addDays(3);
    }

    // Accept stock method
    public function acceptStock(int $quantity, ?string $notes = null): bool
    {
        $remaining = $quantity;

        foreach ($this->stockItems as $item) {
            $needed = $item->stock_quantity - $item->stock_quantity_received;

            if ($needed <= 0) continue; // already fully received

            $toAdd = min($remaining, $needed);

            if ($item->variant) {
                $item->variant->increment('stock_quantity', $toAdd);
            }

            $item->stock_quantity_received += $toAdd;
            $item->save();

            $remaining -= $toAdd;

            if ($remaining <= 0) break; // done distributing quantity
        }

        // Update order status
        $allReceived = $this->stockItems->every(fn($item) => $item->stock_quantity_received >= $item->stock_quantity);

        if ($allReceived) {
            $this->status = 'completed';
        } elseif ($this->pending_quantity < $this->stockItems->sum('stock_quantity')) {
            $this->status = 'partial';
        }

        $this->save();

        return true;
    }
}
