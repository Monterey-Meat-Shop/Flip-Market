<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Shipping;
use App\Models\Address;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutPage extends Component
{
    use WithFileUploads;

    public $paymentScreenshot;
    public $cartItems;
    public $subtotal = 0;
    public $deliveryFee = 70;
    public $discountAmount = 0;
    public $productDiscountSavings = 0; // New: Track product-level discount savings
    public $totalAmount = 0;
    public $cartCount = 0;

    public $selectedShippingMethod = 'JNT';
    public $availableShippingMethods = [];

    public $firstName = '';
    public $lastName = '';
    public $phone = '';
    public $selectedAddressId = null;
    
    public $addressLine1 = '';
    public $addressLine2 = '';
    public $city = '';
    public $province = '';
    public $postalCode = '';
    
    public $availableAddresses = [];

    public $selectedPaymentMethod = null;
    public $gcashReferenceNumber = '';
    public $bankTransferReferenceNumber = '';

    public $discountCode = '';
    public $appliedDiscount = null;

    public $showGcashReference = false;
    public $showBankTransferReference = false;
    public $paymentMethods = [];
    public $customer;
    public $isProcessing = false;

    protected $listeners = ['paymentMethodChanged'];

    protected $rules = [
        'firstName' => 'required|string|max:50',
        'lastName' => 'required|string|max:50',
        'phone' => 'required|string|max:15',
        'selectedAddressId' => 'required|exists:addresses,addressID',
        'selectedPaymentMethod' => 'required|exists:payment_methods,payment_methodID',
        'selectedShippingMethod' => 'required|in:JNT,LALAMOVE',
        'gcashReferenceNumber' => 'nullable|string|max:50',
        'bankTransferReferenceNumber' => 'nullable|string|max:50',
        'discountCode' => 'nullable|string|max:50',
        'paymentScreenshot' => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'firstName.required' => 'First name is required.',
        'lastName.required' => 'Last name is required.',
        'phone.required' => 'Phone number is required.',
        'selectedAddressId.required' => 'Please select a delivery address.',
        'selectedPaymentMethod.required' => 'Please select a payment method.',
        'selectedShippingMethod.required' => 'Please select a shipping method.',
        'gcashReferenceNumber.required' => 'GCash reference number is required.',
        'bankTransferReferenceNumber.required' => 'Bank Transfer reference number is required.',
    ];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->loadCustomerData();
        $this->loadCartData();
        $this->loadPaymentMethods();
        $this->loadShippingMethods();
        $this->calculateTotals();
    }

    /**
     * Helper method to get active discount for a product
     */
    private function getActiveDiscount($product)
    {
        if (!$product || !$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        return $product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                return (is_null($discount->start_date) || $discount->start_date <= now())
                    && (is_null($discount->end_date) || $discount->end_date >= now());
            })
            ->first();
    }

    /**
     * Calculate discounted price
     */
    private function calculateDiscountedPrice($basePrice, $discount)
    {
        if (!$discount) {
            return $basePrice;
        }

        if ($discount->discount_type === 'Percentage') {
            $discountedPrice = $basePrice - ($basePrice * ($discount->discount_value / 100));
        } else {
            $discountedPrice = $basePrice - $discount->discount_value;
        }

        return max($discountedPrice, 0);
    }

    public function loadCustomerData()
    {
        $this->customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$this->customer) {
            session()->flash('error', 'Customer profile not found. Please complete your profile first.');
            return redirect()->route('profile');
        }

        $this->firstName = $this->customer->first_name;
        $this->lastName = $this->customer->last_name;
        $this->phone = $this->customer->phone;

        $this->availableAddresses = $this->customer->address()->get();

        if ($this->availableAddresses->isNotEmpty()) {
            $latestAddress = $this->availableAddresses->first();
            $this->selectedAddressId = $latestAddress->addressID;
            $this->updateAddressFields();
        }
    }

    public function loadCartData()
    {
        if (!$this->customer) {
            $this->cartItems = collect();
            return;
        }

        // Eager load product discounts
        $this->cartItems = CartItem::where('customerID', $this->customer->customerID)
            ->with(['product.discounts', 'variant'])
            ->get();

        if ($this->cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return redirect()->route('cart');
        }

        // Calculate product-level discounts and update prices
        $this->productDiscountSavings = 0;
        
        foreach ($this->cartItems as $item) {
            $basePrice = $item->variant->price ?? $item->product->price;
            $activeDiscount = $this->getActiveDiscount($item->product);
            
            if ($activeDiscount) {
                $discountedPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);
                
                // Calculate savings for this item
                $savings = ($basePrice - $discountedPrice) * $item->quantity;
                $this->productDiscountSavings += $savings;
                
                // Update item with discounted price
                $item->unit_price = $discountedPrice;
                $item->sub_total = $discountedPrice * $item->quantity;
                
                // Store discount info for display
                $item->active_discount = $activeDiscount;
                $item->original_price = $basePrice;
            } else {
                // No discount, use original price
                $item->unit_price = $basePrice;
                $item->sub_total = $basePrice * $item->quantity;
                $item->original_price = $basePrice;
            }
        }

        $this->cartCount = $this->cartItems->sum('quantity');
    }

    public function loadPaymentMethods()
    {
        $excludedMethods = ['Cash', 'cash', 'CASH'];
        
        $this->paymentMethods = PaymentMethod::where('is_active', true)
            ->whereNotIn('method_name', $excludedMethods)
            ->get();
        
        $cashOnDelivery = $this->paymentMethods->where('method_name', 'Cash on Delivery')->first();
        $gcash = $this->paymentMethods->where('method_name', 'GCash')->first();
        
        if ($cashOnDelivery) {
            $this->selectedPaymentMethod = $cashOnDelivery->payment_methodID;
            $this->showGcashReference = false;
            $this->showBankTransferReference = false;
        } elseif ($gcash) {
            $this->selectedPaymentMethod = $gcash->payment_methodID;
            $this->showGcashReference = true;
        } elseif ($this->paymentMethods->isNotEmpty()) {
            $firstMethod = $this->paymentMethods->first();
            $this->selectedPaymentMethod = $firstMethod->payment_methodID;
            $methodName = strtolower($firstMethod->method_name);
            $this->showBankTransferReference = $methodName === 'bank transfer' || $methodName === 'banktransfer';
        }
    }

    public function loadShippingMethods()
    {
        $this->availableShippingMethods = [
            'JNT' => [
                'name' => 'J&T Express',
                'fee' => 70,
                'description' => '3-5 business days delivery',
            ],
            'LALAMOVE' => [
                'name' => 'Lalamove',
                'fee' => 120,
                'description' => 'Same day delivery',
            ],
        ];
        
        $this->selectedShippingMethod = 'JNT';
        $this->updateDeliveryFee();
    }

    public function updatedSelectedAddressId($value)
    {
        $this->updateAddressFields();
    }

    public function updateAddressFields()
    {
        if ($this->selectedAddressId) {
            $address = Address::find($this->selectedAddressId);
            if ($address) {
                $this->addressLine1 = $address->address_line_1;
                $this->addressLine2 = $address->address_line_2;
                $this->city = $address->city;
                $this->province = $address->province;
                $this->postalCode = $address->postal_code;
            }
        }
    }

    public function updatedSelectedShippingMethod($value)
    {
        $this->updateDeliveryFee();
        $this->calculateTotals();
    }

    public function updateDeliveryFee()
    {
        if (isset($this->availableShippingMethods[$this->selectedShippingMethod])) {
            $shippingDetails = $this->availableShippingMethods[$this->selectedShippingMethod];
            $this->deliveryFee = $shippingDetails['fee'];
        } else {
            $this->deliveryFee = 70;
        }
    }

    public function updatedSelectedPaymentMethod($value)
    {
        $paymentMethod = PaymentMethod::find($value);
        
        if ($paymentMethod) {
            $methodName = strtolower($paymentMethod->method_name);
            $this->showGcashReference = $methodName === 'gcash';
            $this->showBankTransferReference = $methodName === 'bank transfer' || $methodName === 'banktransfer';
            
            if ($methodName === 'cash on delivery') {
                $this->showGcashReference = false;
                $this->showBankTransferReference = false;
            }
        }
        
        if (!$this->showGcashReference) {
            $this->gcashReferenceNumber = '';
        }
        if (!$this->showBankTransferReference) {
            $this->bankTransferReferenceNumber = '';
        }
    }

    public function applyDiscount()
    {
        if (empty($this->discountCode)) {
            session()->flash('discount_error', 'Please enter a discount code.');
            return;
        }

        $discount = Discount::where('name', $this->discountCode)
            ->where('is_active', true)
            ->first();

        if (!$discount) {
            session()->flash('discount_error', 'Invalid discount code.');
            return;
        }

        $this->appliedDiscount = $discount;
        $this->calculateTotals();
        session()->flash('discount_success', 'Discount applied successfully!');
    }

    public function removeDiscount()
    {
        $this->appliedDiscount = null;
        $this->discountCode = '';
        $this->calculateTotals();
        session()->flash('discount_success', 'Discount removed.');
    }

    public function calculateTotals()
    {
        // Calculate subtotal with product-level discounts already applied
        $this->subtotal = $this->cartItems->sum('sub_total');
        
        // Apply checkout-level discount (coupon code) if any
        $this->discountAmount = 0;
        if ($this->appliedDiscount) {
            $originalPrice = $this->subtotal;
            $discountedPrice = $this->appliedDiscount->getFinalPrice($originalPrice);
            $this->discountAmount = $originalPrice - $discountedPrice;
        }

        $discountedSubtotal = $this->subtotal - $this->discountAmount;
        $this->totalAmount = $discountedSubtotal + $this->deliveryFee;
    }

    public function validateStock()
    {
        foreach ($this->cartItems as $item) {
            if (!$item->variant) {
                throw new \Exception("Product variant not found for {$item->product->name}. Please refresh and try again.");
            }
            
            $variant = $item->variant->fresh();
            $availableStock = $variant->stock_quantity;
            
            if ($item->quantity > $availableStock) {
                throw new \Exception("Insufficient stock for {$item->product->name} (Size: {$variant->size}). Available: {$availableStock}, Required: {$item->quantity}");
            }
        }
    }

    public function placeOrder()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $this->validate();

            if (empty($this->selectedAddressId)) {
                $this->addError('selectedAddressId', 'Please select a delivery address.');
                $this->isProcessing = false;
                return;
            }

            if ($this->showGcashReference && empty($this->gcashReferenceNumber)) {
                $this->addError('gcashReferenceNumber', 'GCash reference number is required.');
                $this->isProcessing = false;
                return;
            }

            if ($this->showBankTransferReference && empty($this->bankTransferReferenceNumber)) {
                $this->addError('bankTransferReferenceNumber', 'Bank Transfer reference number is required.');
                $this->isProcessing = false;
                return;
            }

            $this->validateStock();

            DB::transaction(function () {
                $this->customer->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'phone' => $this->phone,
                ]);

                $selectedAddress = Address::find($this->selectedAddressId);
                
                $order = Order::create([
                    'customerID' => $this->customer->customerID,
                    'discountID' => $this->appliedDiscount ? $this->appliedDiscount->discountID : null,
                    'order_date' => now(),
                    'total_amount' => $this->subtotal,
                    'final_amount' => $this->totalAmount,
                    'order_status' => 'Pending',
                    'address_choice' => $selectedAddress->address_line_1 . ', ' . $selectedAddress->city,
                    'postal_code' => $selectedAddress->postal_code,
                    'city' => $selectedAddress->city,
                    'province' => $selectedAddress->province,
                ]);

                // Create order items with discounted prices
                foreach ($this->cartItems as $cartItem) {
                    // Use the already calculated discounted prices from cart
                    OrderItem::create([
                        'orderID' => $order->orderID,
                        'productID' => $cartItem->productID,
                        'product_variant_id' => $cartItem->product_variant_id,
                        'size' => $cartItem->size,
                        'colorway' => $cartItem->colorway,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->unit_price, // This is already discounted
                        'sub_total' => $cartItem->sub_total, // This is already discounted
                    ]);

                    if (!$cartItem->variant) {
                        throw new \Exception("Product variant not found for {$cartItem->product->name}");
                    }
                    
                    $affectedRows = DB::table('product_variants')
                        ->where('id', $cartItem->product_variant_id)
                        ->where('stock_quantity', '>=', $cartItem->quantity)
                        ->update([
                            'stock_quantity' => DB::raw("stock_quantity - {$cartItem->quantity}"),
                            'updated_at' => now()
                        ]);
                        
                    if ($affectedRows === 0) {
                        throw new \Exception("Could not update stock for {$cartItem->product->name}. Stock may have been sold out.");
                    }
                }

                $paymentMethod = PaymentMethod::find($this->selectedPaymentMethod);
                $methodName = strtolower($paymentMethod->method_name);

                $screenshotPath = null;
                if ($this->paymentScreenshot) {
                    $screenshotPath = $this->paymentScreenshot->store('screenshots', 'public');
                }
                
                if ($methodName === 'cash on delivery') {
                    $paymentStatus = 'cash_on_delivery';
                    $referenceNumber = null;
                } else {
                    $paymentStatus = 'pending';
                    $referenceNumber = null;
                    
                    if ($this->showGcashReference && $this->gcashReferenceNumber) {
                        $referenceNumber = $this->gcashReferenceNumber;
                    } elseif ($this->showBankTransferReference && $this->bankTransferReferenceNumber) {
                        $referenceNumber = $this->bankTransferReferenceNumber;
                    }
                }
                
                Payment::create([
                    'orderID' => $order->orderID,
                    'payment_methodID' => $this->selectedPaymentMethod,
                    'amount' => $this->totalAmount,
                    'reference_number' => $referenceNumber,
                    'screenshot_path' => $screenshotPath,
                    'status' => 'unpaid',
                ]);

                Shipping::create([
                    'orderID' => $order->orderID,
                    'shipping_method' => $this->selectedShippingMethod,
                    'shipping_status' => 'pending',
                    'shipping_fee' => $this->deliveryFee,
                ]);

                CartItem::where('customerID', $this->customer->customerID)->delete();

                $this->dispatch('cartUpdated', 0);

                session()->flash('order_success', 'Order placed successfully! Order ID: ' . $order->orderID);
                
                return redirect()->route('orders.show', $order->orderID);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isProcessing = false;
            throw $e;
        } catch (\Exception $e) {
            $this->isProcessing = false;
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.checkout-page', [
            'cartItems' => $this->cartItems,
            'subtotal' => $this->subtotal,
            'deliveryFee' => $this->deliveryFee,
            'discountAmount' => $this->discountAmount,
            'productDiscountSavings' => $this->productDiscountSavings,
            'totalAmount' => $this->totalAmount,
            'cartCount' => $this->cartCount,
            'paymentMethods' => $this->paymentMethods,
            'availableAddresses' => $this->availableAddresses,
            'availableShippingMethods' => $this->availableShippingMethods,
            'showGcashReference' => $this->showGcashReference,
            'showBankTransferReference' => $this->showBankTransferReference,
        ]);
    }
}