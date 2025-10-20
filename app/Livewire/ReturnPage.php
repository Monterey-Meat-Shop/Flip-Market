<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class ReturnPage extends Component
{
    public $order;

    public function mount($orderId)
    {
        // Find order by orderID (not id)
        $this->order = Order::with(['orderItems.product', 'customer'])
            ->where('orderID', $orderId)
            ->firstOrFail();

        // Get authenticated user's customer record
        $user = Auth::user();
        $customer = $user->customer; // This uses the relationship from User model
        
        // Verify customer exists
        if (!$customer) {
            session()->flash('error', 'Customer profile not found. Please contact support.');
            return redirect()->route('my.orders');
        }
        
        // Verify the order belongs to the authenticated user's customer
        if ($this->order->customerID !== $customer->customerID) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Check if order is eligible for return
        if (!$this->order->is_returnable) {
            session()->flash('error', 'This order is not eligible for return.');
            return redirect()->route('my.orders');
        }

        // Check if order status allows returns
        $allowedStatuses = ['completed', 'processing'];
        if (!in_array($this->order->order_status, $allowedStatuses)) {
            session()->flash('error', 'Returns can only be requested for completed or processing orders.');
            return redirect()->route('my.orders');
        }

        // Check if return deadline has passed
        if ($this->order->return_deadline && now()->isAfter($this->order->return_deadline)) {
            session()->flash('error', 'The return deadline for this order has passed.');
            return redirect()->route('my.orders');
        }

        // Check if return already exists
        if ($this->order->returnRequest()->exists()) {
            session()->flash('error', 'A return request already exists for this order.');
            return redirect()->route('my.orders');
        }
    }

    public function render()
    {
        return view('livewire.return-page', [
            'order' => $this->order,
        ])->layout('components.layouts.app');
    }
}