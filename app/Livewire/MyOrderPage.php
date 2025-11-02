<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class MyOrderPage extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $searchQuery = '';
    public $customer;

    protected $queryString = array(
        'activeTab' => array('except' => 'all'),
        'searchQuery' => array('except' => ''),
    );

    public function mount($tab = 'all')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->customer = Customer::where('user_id', Auth::id())->first();

        if (!$this->customer) {
            session()->flash('error', 'Customer profile not found.');
            return redirect()->route('profile');
        }

        $this->activeTab = $tab;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        if (!$this->customer) {
            return collect();
        }

        $query = Order::where('customerID', $this->customer->customerID)
            ->with(array('orderItems.product', 'orderItems.productVariant', 'payment', 'shipping', 'returnRequest'))
            ->orderBy('order_date', 'desc');

        // Apply search filter
        if (!empty($this->searchQuery)) {
            $query->where(function($q) {
                $q->where('orderID', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('orderItems.product', function($productQuery) {
                      $productQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        // Apply status filter
        switch ($this->activeTab) {
            case 'to_pay':
                $query->whereIn('order_status', array('Pending'))
                      ->whereHas('payment', function($paymentQuery) {
                          $paymentQuery->where('status', '!=', 'Cash on Delivery');
                      });
                break;
            case 'processing':
               $query->whereHas('shipping', function($shippingQuery) {
                    $shippingQuery->where('shipping_status', 'processing');
                });
                break;
            case 'in_transit':
               $query->whereHas('shipping', function($shippingQuery) {
                    $shippingQuery->where('shipping_status', 'in_transit');
                });
                break;
            case 'to_receive':
                $query->whereHas('shipping', function($shippingQuery) {
                    $shippingQuery->where('shipping_status', 'in_transit');
                });
                break;
            case 'completed':
                $query->where('order_status', 'Completed');
                break;
            case 'cancelled':
                $query->where('order_status', 'Cancelled');
                break;
            case 'returned':
                $query->whereIn('order_status', array('return_requested', 'returned'));
                break;
            case 'failed':
                $query->where('order_status', 'Failed');
                break;
            default: // 'all'
                break;
        }

        return $query->paginate(10);
    }

    public function getStatusColor($order)
    {
        switch (strtolower($order->order_status)) {
            case 'pending':
                // Check if it's cash on delivery or needs payment
                if ($order->payment && $order->payment->status === 'Cash on Delivery') {
                    return array('bg-blue-100', 'text-blue-600', 'To Ship');
                } else {
                    return array('bg-orange-100', 'text-orange-600', 'To Pay');
                }
            case 'processing':
                return array('bg-blue-100', 'text-blue-600', 'Processing');
                
            case 'confirmed':
                return array('bg-yellow-100', 'text-yellow-600', 'To Ship');
            case 'shipped':
                return array('bg-purple-100', 'text-purple-600', 'To Receive');
            case 'completed':
            case 'delivered':
                return array('bg-green-100', 'text-green-600', 'Completed');
            case 'cancelled':
                return array('bg-red-100', 'text-red-600', 'Cancelled');
            case 'return_requested':
                return array('bg-yellow-100', 'text-yellow-600', 'Return Requested');
            case 'returned':
                return array('bg-gray-100', 'text-red-600', 'Returned');
            case 'failed':
                return array('bg-red-100', 'text-red-600', 'Failed');
            default:
                return array('bg-gray-100', 'text-gray-600', 'Unknown');
        }
    }

    public function viewOrderDetails($orderId)
    {
        return redirect()->route('orders.show', $orderId);
    }

    public function cancelOrder($orderId)
    {
        $order = Order::where('orderID', $orderId)
            ->where('customerID', $this->customer->customerID)
            ->first();

        if (!$order) {
            session()->flash('error', 'Order not found.');
            return;
        }

        // Only allow cancellation for pending orders
        if ($order->order_status !== 'Pending') {
            session()->flash('error', 'This order cannot be cancelled.');
            return;
        }

        // Return stock to products/variants
        foreach ($order->orderItems as $item) {
            if ($item->productVariant) {
                $item->productVariant->increment('stock_quantity', $item->quantity);
            } else {
                $item->product->increment('total_stock_quantity', $item->quantity);
            }
        }

        $order->update(array('order_status' => 'Cancelled'));

        if ($order->payment) {
            $order->payment->update(array('status' => 'Cancelled'));
        }

        session()->flash('success', 'Order cancelled successfully.');
        $this->resetPage();
    }

    public function requestReturn($orderId)
    {
        $order = Order::where('orderID', $orderId)
            ->where('customerID', $this->customer->customerID)
            ->with('returnRequest')
            ->first();

        if (!$order) {
            session()->flash('error', 'Order not found.');
            return;
        }

        // Check if return already exists
        if ($order->returnRequest) {
            session()->flash('error', 'A return request already exists for this order.');
            return;
        }

        // Only allow returns for completed orders
        if (!in_array(strtolower($order->order_status), ['completed', 'delivered'])) {
            session()->flash('error', 'Only completed orders can be returned.');
            return;
        }

        // Check if order is eligible for return
        if (isset($order->is_returnable) && !$order->is_returnable) {
            session()->flash('error', 'This order is not eligible for return.');
            return;
        }

        // Check return deadline if it exists
        if (isset($order->return_deadline) && $order->return_deadline && now()->isAfter($order->return_deadline)) {
            session()->flash('error', 'The return deadline for this order has passed.');
            return;
        }

        // Redirect to return page with order details
        return redirect()->route('return.page', ['orderId' => $orderId]);
    }

    public function render()
    {
        return view('livewire.my-order-page', array(
            'orders' => $this->orders,
        ));
    }
}