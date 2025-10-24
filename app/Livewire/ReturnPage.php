<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\Auth;

class ReturnPage extends Component
{
    use WithFileUploads;
    
    public $order;
    public $selectedItems = [];
    public $itemQuantities = [];
    public $condition;
    public $other_reason;
    public $product_image;

    protected $rules = [
        'condition' => 'required|string|in:not_delivered,defective,changed_mind,incorrect,other',
        'other_reason' => 'required_if:condition,other|nullable|string|max:1000',
        'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];

    protected $messages = [
        'product_image.required' => 'Please upload a product image.',
        'product_image.image' => 'The file must be an image.',
        'product_image.max' => 'The image size must not exceed 2MB.',
        'condition.required' => 'Please select a reason for the return.',
        'other_reason.required_if' => 'Please provide details for "Other Reason".',
    ];

    public function mount($orderId)
    {
        // Find order by orderID (not id)
        $this->order = Order::with(['orderItems.product', 'orderItems.productVariant', 'customer'])
            ->where('orderID', $orderId)
            ->firstOrFail();

        // Get authenticated user's customer record
        $user = Auth::user();
        $customer = $user->customer;
        
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
        if (isset($this->order->is_returnable) && !$this->order->is_returnable) {
            session()->flash('error', 'This order is not eligible for return.');
            return redirect()->route('my.orders');
        }

        // Check if order status allows returns
        $allowedStatuses = ['completed', 'processing', 'Completed', 'Processing'];
        if (!in_array($this->order->order_status, $allowedStatuses)) {
            session()->flash('error', 'Returns can only be requested for completed or processing orders.');
            return redirect()->route('my.orders');
        }

        // Check if return deadline has passed
        if (isset($this->order->return_deadline) && $this->order->return_deadline && now()->isAfter($this->order->return_deadline)) {
            session()->flash('error', 'The return deadline for this order has passed.');
            return redirect()->route('my.orders');
        }

        // Check if return already exists
        if ($this->order->returnRequest()->exists()) {
            session()->flash('error', 'A return request already exists for this order.');
            return redirect()->route('my.orders');
        }
        
        // Initialize all items as selected with their full quantity by default
        foreach ($this->order->orderItems as $item) {
        // Use DB primary key: order_itemID
        $id = $item->order_itemID;
        $this->selectedItems[] = $id;
        $this->itemQuantities[$id] = $item->quantity;
        }
    }
    
    public function toggleItem($itemId)
    {
        $itemId = (int) $itemId;

        if (in_array($itemId, $this->selectedItems)) {
            $this->selectedItems = array_filter($this->selectedItems, fn($id) => $id !== $itemId);
        } else {
            $this->selectedItems[] = $itemId;
        }
        // reindex
        $this->selectedItems = array_values($this->selectedItems);
    }
    
    public function updatedItemQuantities($value, $key)
    {
        if (!in_array($key, $this->selectedItems)) {
            $this->selectedItems[] = $key;
        }
    }
    
    public function submitReturn()
    {
        // Validate selected items
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Please select at least one item to return.');
            $this->dispatch('scroll-to-top');
            return;
        }
        
        // Validate form
        $this->validate();
        
        // Handle file upload
        $imagePath = null;
        if ($this->product_image) {
            $imagePath = $this->product_image->store('returns', 'public');
        }
        
        // Build returned items data
        $returnedItemsData = [];
        foreach ($this->selectedItems as $order_itemID) {   // note variable name
            // extra guard: ensure it exists and belongs to this order
            $orderItem = OrderItem::where('order_itemID', $order_itemID)
                        ->where('orderID', $this->order->orderID)
                        ->first();

            if (! $orderItem) {
                // skip or throw — better to abort and show error
                session()->flash('error', "One of the selected items is invalid.");
                return;
            }

            $returnedItemsData[] = [
                'order_itemID' => $order_itemID,                    // store with DB-style key
                'quantity' => (int) ($this->itemQuantities[$order_itemID] ?? 1),
            ];
        }
        
        // Get authenticated user's customer
        $user = Auth::user();
        $customer = $user->customer;
        
        // Create return request
        $return = ReturnRequest::create([
            'orderID' => $this->order->orderID,
            'customerID' => $customer->customerID,
            'return_reason' => $this->condition,
            'other_reason' => $this->other_reason,
            'product_image' => $imagePath,
            'return_status' => 'pending',
            'returned_items' => $returnedItemsData,
        ]);
        
        // Update order status
        $this->order->update([
            'order_status' => 'return_requested'
        ]);
        
        return redirect()->route('returns.confirmation')
            ->with('success', 'Your return request has been submitted successfully!');
    }

    public function render()
    {
        return view('livewire.return-page', [
            'order' => $this->order,
        ])->layout('components.layouts.app');
    }
}