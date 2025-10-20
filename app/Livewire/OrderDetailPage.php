<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class OrderDetailPage extends Component
{
    public $order;
    public $orderId;
    
    public function mount($orderId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$customer) {
            session()->flash('error', 'Customer profile not found.');
            return redirect()->route('profile');
        }

        $this->orderId = $orderId;

        // Find the order and verify it belongs to this customer
        $this->order = Order::where('orderID', $orderId)
            ->where('customerID', $customer->customerID)
            ->with(['orderItems.product', 'orderItems.productVariant', 'payment.paymentMethod', 'shipping', 'customer'])
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.order-detail-page');
    }
}