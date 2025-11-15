<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Shipping;
use App\Models\Address;
use App\Models\Discount;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

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

    // NEW: COD Downpayment Calculation
    public $totalItems = 0;
    public $codDownpaymentAmount = 0;
    public $codDownpaymentPerItem = 300; // ₱300 per item

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

    public $showCodDownPayment = false;
    public $codDownPaymentMethod = null;
    public $codGcashReferenceNumber = '';
    public $codBankReferenceNumber = '';

    // Lalamove-specific
    public $lalamoveBookingOption = null;
    public $lalamoveFee = null;
    public $lalamoveTracking = null;

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
        $this->calculateCodDownpayment(); // NEW
    }

    private function getActiveDiscount($product)
    {
        if (!$product || !$product->relationLoaded('discounts')) {
            $product->load('discounts');
        }

        return $product->discounts
            ->where('is_active', true)
            ->filter(function ($discount) {
                $now = now();
                $startDateValid = is_null($discount->start_date) || $discount->start_date <= $now;
                $endDateValid = is_null($discount->end_date) || $discount->end_date >= $now;
                $hasValidValue = $discount->discount_value > 0;

                return $startDateValid && $endDateValid && $hasValidValue;
            })
            ->first();
    }

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
            return $basePrice;
        }

        return max($discountedPrice, 0);
    }

    // NEW: Calculate COD Downpayment based on total items
    private function calculateCodDownpayment()
    {
        $this->totalItems = 0;

        foreach ($this->cartItems as $item) {
            $this->totalItems += $item->quantity;
        }

        // Calculate: ₱300 per item
        $this->codDownpaymentAmount = $this->totalItems * $this->codDownpaymentPerItem;
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

        $selectedIds = session('checkout_items', []);

        $query = CartItem::where('customerID', $this->customer->customerID)
            ->with(['product.discounts', 'variant']);

        if (!empty($selectedIds)) {
            $query->whereIn('cart_itemID', $selectedIds);
        }

        $this->cartItems = $query->get();

        if ($this->cartItems->isEmpty()) {
            session()->flash('error', 'No items selected for checkout.');
            return redirect()->route('cart');
        }

        $this->productDiscountSavings = 0;
        $this->originalSubtotal = 0;
        $this->discountedSubtotal = 0;

        foreach ($this->cartItems as $item) {
            $basePrice = (float) $item->product->price;
            $activeDiscount = $this->getActiveDiscount($item->product);

            Log::info('Processing cart item', [
                'product' => $item->product->name,
                'base_price' => $basePrice,
                'has_discount' => !is_null($activeDiscount),
                'discount_name' => $activeDiscount?->name ?? 'none',
                'discount_value' => $activeDiscount?->discount_value ?? 0,
                'discount_type' => $activeDiscount?->discount_type ?? 'none',
            ]);

            $item->unit_price = round($basePrice, 2);
            $item->sub_total = round($basePrice * $item->quantity, 2);
            $item->original_price = null;
            $item->discount_amount = 0;
            $item->active_discount = null;

            if ($activeDiscount && $activeDiscount->discount_value > 0) {
                $discountedPrice = $this->calculateDiscountedPrice($basePrice, $activeDiscount);
                $discountDifference = $basePrice - $discountedPrice;

                Log::info('Discount calculation', [
                    'base' => $basePrice,
                    'discounted' => $discountedPrice,
                    'difference' => $discountDifference,
                ]);

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

        // Recalculate COD downpayment after cart data is loaded
        $this->calculateCodDownpayment();
    }

    public function loadPaymentMethods()
    {
        $excludedMethods = ['Cash', 'cash', 'CASH'];

        $this->paymentMethods = PaymentMethod::where('is_active', true)
            ->whereNotIn('method_name', $excludedMethods)
            ->get();

        $cod = $this->paymentMethods->where('method_name', 'Cash on Delivery')->first();
        $gcash = $this->paymentMethods->where('method_name', 'GCash')->first();
        $bank = $this->paymentMethods->where('method_name', 'Bank Transfer')->first();

        if ($cod) {
            $this->selectedPaymentMethod = $cod->payment_methodID;
            $this->showCodDownPayment = true;
            $this->codDownPaymentMethod = null;
        } elseif ($gcash) {
            $this->selectedPaymentMethod = $gcash->payment_methodID;
            $this->showGcashReference = true;
        } elseif ($bank) {
            $this->selectedPaymentMethod = $bank->payment_methodID;
            $this->showBankTransferReference = true;
        }
    }

    public function loadShippingMethods()
    {
        $this->availableShippingMethods = [
            'JNT' => [
                'name' => 'J&T Express',
                'fee' => 100,
                'description' => '3-5 business days delivery',
            ],
            'LALAMOVE' => [
                'name' => 'Lalamove',
                'fee' => 0,
                'description' => 'Same day delivery (fee depends on location)',
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
        if ($value !== 'LALAMOVE') {
            $this->lalamoveBookingOption = null;
            $this->lalamoveFee = null;
            $this->lalamoveTracking = null;
        }

        $this->updateDeliveryFee();
        $this->calculateTotals();
    }

    public function updatedLalamoveBookingOption()
    {
        $this->updateDeliveryFee();
        $this->calculateTotals();
    }

    public function updatedLalamoveFee()
    {
        $this->updateDeliveryFee();
        $this->calculateTotals();
    }

    public function updateDeliveryFee()
    {
        if ($this->selectedShippingMethod === 'JNT') {
            $this->deliveryFee = $this->availableShippingMethods['JNT']['fee'] ?? 100;
            return;
        }

        if ($this->selectedShippingMethod === 'LALAMOVE') {
            if (in_array($this->lalamoveBookingOption, ['customer', 'store'])) {
                $this->deliveryFee = 0;
                return;
            }

            $this->deliveryFee = 0;
            return;
        }

        $this->deliveryFee = 100;
    }

    public function updatedSelectedPaymentMethod($value)
    {
        $paymentMethod = PaymentMethod::find($value);
        if (!$paymentMethod) return;

        $methodName = strtolower($paymentMethod->method_name);

        // Reset all
        $this->showGcashReference = false;
        $this->showBankTransferReference = false;
        $this->showCodDownPayment = false;

        // Reset reference numbers
        $this->gcashReferenceNumber = '';
        $this->bankTransferReferenceNumber = '';
        $this->codGcashReferenceNumber = '';
        $this->codBankReferenceNumber = '';
        $this->codDownPaymentMethod = null;

        if ($methodName === 'gcash') {
            $this->showGcashReference = true;
        } elseif ($methodName === 'bank transfer' || $methodName === 'banktransfer') {
            $this->showBankTransferReference = true;
        } elseif ($methodName === 'cash on delivery') {
            $this->showCodDownPayment = true;
        }
    }

    // NEW: Handle COD downpayment method changes
    public function updatedCodDownPaymentMethod($value)
    {
        // Clear reference numbers when switching COD downpayment method
        $this->codGcashReferenceNumber = '';
        $this->codBankReferenceNumber = '';
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
        $this->subtotal = $this->cartItems->sum('sub_total');

        // new fix for cart count
        $this->cartCount = $this->cartItems->sum('quantity');

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

            // NEW: COD Downpayment Validation
            $paymentMethod = PaymentMethod::find($this->selectedPaymentMethod);
            $methodName = strtolower($paymentMethod->method_name ?? '');

            if ($methodName === 'cash on delivery') {
                if (empty($this->codDownPaymentMethod)) {
                    $this->addError('codDownPaymentMethod', 'Please choose a downpayment method for COD.');
                    $this->isProcessing = false;
                    return;
                }

                if ($this->codDownPaymentMethod === 'gcash' && empty($this->codGcashReferenceNumber)) {
                    $this->addError('codGcashReferenceNumber', 'GCash reference number is required for downpayment.');
                    $this->isProcessing = false;
                    return;
                }

                if ($this->codDownPaymentMethod === 'bank_transfer' && empty($this->codBankReferenceNumber)) {
                    $this->addError('codBankReferenceNumber', 'Bank transfer reference number is required for downpayment.');
                    $this->isProcessing = false;
                    return;
                }
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

            if ($this->selectedShippingMethod === 'LALAMOVE') {
                if (!$this->lalamoveBookingOption) {
                    $this->addError('lalamoveBookingOption', 'Please choose who will book the Lalamove delivery.');
                    $this->isProcessing = false;
                    return;
                }

                if (!in_array($this->lalamoveBookingOption, ['customer', 'store'])) {
                    $this->addError('lalamoveBookingOption', 'Invalid Lalamove booking option selected.');
                    $this->isProcessing = false;
                    return;
                }

                $this->lalamoveFee = null;
                $this->updateDeliveryFee();
                $this->calculateTotals();
            }

            if ($methodName !== 'cash on delivery' && !$this->paymentScreenshot) {
                $this->addError('paymentScreenshot', 'A payment screenshot is required for this payment method.');
                $this->isProcessing = false;
                return;
            }

            $this->validateStock();

            $createdOrderId = null;

            DB::transaction(function () use (&$createdOrderId, $methodName) {
                $this->customer->update([
                    'first_name' => $this->firstName,
                    'last_name'  => $this->lastName,
                    'phone'      => $this->phone,
                ]);

                $selectedAddress = Address::find($this->selectedAddressId);

                $order = Order::create([
                    'customerID'     => $this->customer->customerID,
                    'discountID'     => $this->appliedDiscount ? $this->appliedDiscount->discountID : null,
                    'order_date'     => now(),
                    'total_amount'   => $this->subtotal,
                    'final_amount'   => $this->totalAmount,
                    'order_status'   => 'Pending',
                    'address_choice' => $selectedAddress->address_line_1 . ', ' . $selectedAddress->city,
                    'postal_code'    => $selectedAddress->postal_code,
                    'city'           => $selectedAddress->city,
                    'province'       => $selectedAddress->province,
                    'stock_deducted' => true,
                ]);

                $createdOrderId = $order->orderID;

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
                        }

                        $originalPrice = ($unitPrice * $cartItem->quantity) + $discountAmount;
                    } else {
                        $originalPrice = $unitPrice * $cartItem->quantity;
                    }

                    $subTotal = $unitPrice * $cartItem->quantity;

                    OrderItem::create([
                        'orderID'           => $order->orderID,
                        'productID'         => $cartItem->productID,
                        'product_variant_id'=> $cartItem->product_variant_id,
                        'discountID'        => $activeDiscount?->discountID,
                        'size'              => $cartItem->size,
                        'colorway'          => $cartItem->colorway,
                        'quantity'          => $cartItem->quantity,
                        'unit_price'        => $unitPrice,
                        'original_price'    => $originalPrice,
                        'discount_name'     => $activeDiscount?->name,
                        'discount_amount'   => $discountAmount,
                        'sub_total'         => $subTotal,
                    ]);

                    if ($cartItem->product_variant_id) {
                        $variant = \App\Models\ProductVariant::find($cartItem->product_variant_id);
                        if ($variant) {
                            $newStock = max($variant->stock_quantity - $cartItem->quantity, 0);
                            $variant->update(['stock_quantity' => $newStock]);
                        }
                    }    
                }

                $screenshotPath = null;
                if ($this->paymentScreenshot) {
                    $screenshotPath = $this->paymentScreenshot->store('screenshots', 'public');
                }

                // UPDATED: Build reference number with COD downpayment method prefix
                $referenceNumber = null;
                if ($methodName === 'cash on delivery') {
                    if ($this->codDownPaymentMethod === 'gcash') {
                        $referenceNumber = '[GCASH] ' . $this->codGcashReferenceNumber;
                    } elseif ($this->codDownPaymentMethod === 'bank_transfer') {
                        $referenceNumber = '[BANK] ' . $this->codBankReferenceNumber;
                    }
                } else {
                    if ($this->showGcashReference && $this->gcashReferenceNumber) {
                        $referenceNumber = $this->gcashReferenceNumber;
                    } elseif ($this->showBankTransferReference && $this->bankTransferReferenceNumber) {
                        $referenceNumber = $this->bankTransferReferenceNumber;
                    }
                }

                Payment::create([
                    'orderID'           => $order->orderID,
                    'payment_methodID'  => $this->selectedPaymentMethod,
                    'amount'            => $this->totalAmount,
                    'reference_number'  => $referenceNumber,
                    'screenshot_path'   => $screenshotPath ?? null,
                    'status'            => 'unpaid',
                ]);

                Shipping::create([
                    'orderID'                => $order->orderID,
                    'shipping_method'        => $this->selectedShippingMethod,
                    'shipping_status'        => 'pending',
                    'shipping_fee'           => $this->deliveryFee,
                    'lalamove_booking_option'=> $this->selectedShippingMethod === 'LALAMOVE'
                        ? $this->lalamoveBookingOption
                        : null,
                    'lalamove_tracking'      => $this->selectedShippingMethod === 'LALAMOVE'
                        ? $this->lalamoveTracking
                        : null,
                ]);

                try {
                    $order->refresh();
                    $order->load(['customer', 'payment', 'orderItems']);

                    $admins = User::role(['admin', 'manager'])->get();

                    if ($admins->count() > 0) {
                        Notification::send($admins, new NewOrderNotification($order));
                        Log::info("Bell notification sent to {$admins->count()} admin(s)/manager(s) for Order #{$order->orderID}");
                    } else {
                        Log::warning("No admins or managers found to notify for Order #{$order->orderID}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send notification for Order #{$order->orderID}: " . $e->getMessage());
                }

                CartItem::where('customerID', $this->customer->customerID)->delete();
                $this->dispatch('cartUpdated', 0);
            });

            if ($createdOrderId) {
                session()->flash('order_success', 'Order placed successfully! Order ID: ' . $createdOrderId);
                $this->isProcessing = false;
                return redirect()->route('orders.show', $createdOrderId);
            }
    
            $this->isProcessing = false;
            session()->flash('error', 'Failed to create order. Please try again.');

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
            'cartItems'                => $this->cartItems,
            'subtotal'                 => $this->subtotal,
            'deliveryFee'              => $this->deliveryFee,
            'discountAmount'           => $this->discountAmount,
            'productDiscountSavings'   => $this->productDiscountSavings,
            'totalAmount'              => $this->totalAmount,
            'cartCount'                => $this->cartCount,
            'paymentMethods'           => $this->paymentMethods,
            'availableAddresses'       => $this->availableAddresses,
            'availableShippingMethods' => $this->availableShippingMethods,
            'showGcashReference'       => $this->showGcashReference,
            'showBankTransferReference'=> $this->showBankTransferReference,
            'showCodDownPayment'       => $this->showCodDownPayment,
            'codDownPaymentMethod'     => $this->codDownPaymentMethod,
            'codGcashReferenceNumber'  => $this->codGcashReferenceNumber,
            'codBankReferenceNumber'   => $this->codBankReferenceNumber,
            'totalItems'               => $this->totalItems, // NEW
            'codDownpaymentAmount'     => $this->codDownpaymentAmount, // NEW
        ]);
    }
}