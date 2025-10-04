<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display the specified order.
     */
    public function show($orderId)
    {
        // Get the authenticated user's customer record
        $customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$customer) {
            return redirect()->route('profile')->with('error', 'Customer profile not found.');
        }

        // Find the order and verify it belongs to this customer
        $order = Order::where('orderID', $orderId)
            ->where('customerID', $customer->customerID)
            ->with(['orderItems.product', 'orderItems.productVariant', 'payment.paymentMethod', 'shipping'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$customer) {
            return redirect()->route('profile')->with('error', 'Customer profile not found.');
        }

        $orders = Order::where('customerID', $customer->customerID)
            ->with(['orderItems.product', 'payment', 'shipping'])
            ->orderBy('order_date', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}