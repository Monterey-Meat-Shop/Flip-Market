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
    public $productDiscountSavings = 0;
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
        $this->loadPaymentMethods();
        $this->loadShippingMethods();
        $this->loadCartData();
        $this->calculateTotals();
    }

    //get active discount for a product
    private function getActiveDiscount($product)
    {
        if (!$product || !$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        $discount = $product->discounts
            ->where('is_active', true)
            ->filter(function($discount) {
                $now = now();
                $startDateValid = is_null($discount->start_date) || $discount->start_date <= $now;
                $endDateValid = is_null($discount->end_date) || $discount->end_date >= $now;
                $hasValidValue = $discount->discount_value > 0;
            
                return $startDateValid && $endDateValid && $hasValidValue;
            })
            ->first();

            return $discount;
    }

    // Calculate discounted price
    private function calculateDiscountedPrice($basePrice, $discount)
    {
        if (!$discount) {
            return $basePrice;
        }

        if ($discount->discount_type === 'Percentage') {
            $discountedPrice = $basePrice - ($basePrice * ($discount->discount_value / 100));
        } elseif ($discount->discount_type === 'Fixed') {
            $discountedPrice = $basePrice - $discount->discount_value;
        } else {
            // If discount type is unknown, return base price (no discount)
            return $basePrice;
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

        // load product discounts
        $this->cartItems = CartItem::where('customerID', $this->customer->customerID)
            ->with(['product.discounts', 'variant'])
            ->get();

        if ($this->cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return redirect()->route('cart');
        }

        // Calculate product-level discounts and update prices
        $this->productDiscountSavings = 0;
        $this->originalSubtotal = 0;
        $this->discountedSubtotal = 0;

        foreach ($this->cartItems as $item) {
            $basePrice = (float) $item->product->price;
            $activeDiscount = $this->getActiveDiscount($item->product);

            \Log::info('Processing cart item', [
                'product' => $item->product->name,
                'base_price' => $basePrice,
                'has_discount' => !is_null($activeDiscount),
                'discount_name' => $activeDiscount?->name ?? 'none',
                'discount_value' => $activeDiscount?->discount_value ?? 0,
                'discount_type' => $activeDiscount?->discount_type ?? 'none',
            ]);

            // Default values (no discount)
            $item->unit_price = round($basePrice, 2);
            $item->sub_total = round($basePrice * $item->quantity, 2);
            $item->original_price = null;
            $item->discount_amount = 0;
            $item->active_discount = null;

            // Only apply discount if one exists AND is valid
            if ($activeDiscount && $activeDiscount->discount_value > 0) {
                $discountedPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);
                $discountDifference = $basePrice - $discountedPrice;

                \Log::info('Discount calculation', [
                    'base' => $basePrice,
                    'discounted' => $discountedPrice,
                    'difference' => $discountDifference,
                ]);

                // Only apply if there's actual savings (more than 1 cent)
                if ($discountDifference > 0.01) {
                    $item->active_discount = $activeDiscount;
                    $item->unit_price = round($discountedPrice, 2);
                    $item->sub_total = round($discountedPrice * $item->quantity, 2);
                    $item->original_price = round($basePrice, 2);
                    $item->discount_amount = round($discountDifference, 2);

                    $this->productDiscountSavings += $discountDifference * $item->quantity;
                }
            }

            $this->originalSubtotal += $basePrice * $item->quantity;
        }
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

                // Create order items
                foreach ($this->cartItems as $cartItem) {
                    $product = $cartItem->product()->with('discounts')->first();
                    $activeDiscount = $this->getActiveDiscount($product);

                    $originalPrice = (float) $product->price;
                    $discountAmount = 0;
                    $unitPrice = $originalPrice;

                    if ($activeDiscount) {
                        $discountType = strtolower($activeDiscount->discount_type);

                        if ($discountType === 'percentage') {
                            $discountAmount = ($unitPrice * ($activeDiscount->discount_value / 100)) * $cartItem->quantity;
                        } elseif ($discountType === 'fixed') {
                            $discountAmount = (float) $activeDiscount->discount_value * $cartItem->quantity;
                        } else {
                            $discountAmount = 0;
                        }
                        $originalPrice = ($unitPrice * $cartItem->quantity) + $discountAmount;

                    } else {
                        $discountAmount = 0;
                        $originalPrice = $unitPrice * $cartItem->quantity;
                    }

                    $subTotal = $unitPrice * $cartItem->quantity;

                    OrderItem::create([
                        'orderID' => $order->orderID,
                        'productID' => $cartItem->productID,
                        'product_variant_id' => $cartItem->product_variant_id,
                        'discountID' => $activeDiscount?->discountID,
                        'size' => $cartItem->size,
                        'colorway' => $cartItem->colorway,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $unitPrice,
                        'original_price' => $originalPrice,
                        'discount_name' => $activeDiscount?->name,
                        'discount_amount' => $discountAmount,
                        'sub_total' => $subTotal,
                    ]);

                    // update stock
                    if ($cartItem->product_variant_id) {
                        $variant = \App\Models\ProductVariant::find($cartItem->product_variant_id);
                        if ($variant) {
                            $newStock = max($variant->stock_quantity - $cartItem->quantity, 0);
                            $variant->update(['stock_quantity' => $newStock]);
                        }
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