<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    /**
     * Submit a return request
     */
    public function submit(Request $request, Order $order)
    {
        // Get authenticated user's customer
        $user = Auth::user();
        $customer = $user->customer;
        
        // Verify customer exists - if not, create one
        if (!$customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => $user->name ?? 'Guest',
                'last_name' => $user->last_name ?? '',
                'phone' => $user->phone ?? '',
            ]);
        }
        
        // Verify the order belongs to the authenticated user
        if ($order->customerID !== $customer->customerID) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Check if order is eligible for return
        if (!$order->is_returnable) {
            return back()->with('error', 'This order is not eligible for return.');
        }

        // Check if return deadline has passed
        if ($order->return_deadline && now()->isAfter($order->return_deadline)) {
            return back()->with('error', 'The return deadline for this order has passed.');
        }

        // Check if return already exists
        if ($order->returnRequest()->exists()) {
            return back()->with('error', 'A return request already exists for this order.');
        }

        // Validate the request
        $validated = $request->validate([
            'condition' => 'required|string|in:not_delivered,defective,changed_mind,incorrect,other',
            'other_reason' => 'required_if:condition,other|nullable|string|max:1000',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('returns', 'public');
        }

        // Get selected items from session
        $selectedItems = session('return_selected_items', []);
        $itemQuantities = session('return_item_quantities', []);
        $itemNotes = session('return_item_notes', []);
        
        if (empty($selectedItems)) {
            return back()->with('error', 'No items selected for return.');
        }
        
        // Build returned items data
        $returnedItemsData = [];
        foreach ($selectedItems as $order_itemID) {
            // ensure integer
            $order_itemID = (int) $order_itemID;

            // ensure the order item exists and belongs to this order
            $orderItem = \App\Models\OrderItem::where('order_itemID', $order_itemID)
                ->where('orderID', $order->orderID)
                ->first();

            if (! $orderItem) {
                return back()->with('error', 'Invalid item selected for return.');
            }

            $returnedItemsData[] = [
                'order_itemID' => $order_itemID,
                'quantity' => (int) ($itemQuantities[$order_itemID] ?? 1),
                'notes' => $itemNotes[$order_itemID] ?? null,
            ];
        }
        
        // Create return request
        $return = ReturnRequest::create([
            'orderID' => $order->orderID,
            'customerID' => $customer->customerID,
            'return_reason' => $validated['condition'],
            'other_reason' => $validated['other_reason'] ?? null,
            'product_image' => $imagePath,
            'return_status' => 'pending',
            'returned_items' => $returnedItemsData,
        ]);
        
        // Clear session data
        session()->forget(['return_selected_items', 'return_item_quantities', 'return_item_notes', 'return_order_id']);

        // Update order status
        $order->update([
            'order_status' => 'return_requested'
        ]);

        return redirect()->route('returns.confirmation')
            ->with('success', 'Your return request has been submitted successfully!');
    }

    /**
     * Show a specific return request
     */
    public function show($returnId)
    {
        // Get authenticated user's customer
        $user = Auth::user();
        $customer = $user->customer;
    
        if (!$customer) {
            return redirect()->route('my.orders')->with('error', 'Customer profile not found.');
        }
    
        // Get the return request for this customer
        $return = \App\Models\ReturnRequest::with([
            'order.orderItems.product',
            'order.orderItems.productVariant',
            'customer'
        ])
        ->where('returnID', $returnId)
        ->where('customerID', $customer->customerID)
        ->firstOrFail();

        return view('livewire.return-view-page', compact('return'));
    }

    /**
     * Show return confirmation page
     */
    public function confirmation()
    {
        return view('returns.confirmation');
    }
}