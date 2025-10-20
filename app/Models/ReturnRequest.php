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
    ];

    /**
     * Get the order that owns the return request
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    /**
     * Get the customer who requested the return
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    /**
     * Get human-readable return reason
     */
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

    /**
     * Get human-readable return status
     */
    public function getReturnStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
            'refunded' => 'Refunded',
        ];

        return $statuses[$this->return_status] ?? $this->return_status;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-green-100 text-green-800',
            'refunded' => 'bg-purple-100 text-purple-800',
        ];

        return $colors[$this->return_status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Scope for pending returns
     */
    public function scopePending($query)
    {
        return $query->where('return_status', 'pending');
    }

    /**
     * Scope for approved returns
     */
    public function scopeApproved($query)
    {
        return $query->where('return_status', 'approved');
    }
}