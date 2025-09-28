<?php

namespace App\Livewire;

use Livewire\Component;
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
    public $cartItems;
    public $subtotal = 0;
    public $deliveryFee = 70;
    public $discountAmount = 0;
    public $totalAmount = 0;
    public $cartCount = 0;

    public $selectedShippingMethod = 'JNT';
    public $availableShippingMethods = array();

    public $firstName = '';
    public $lastName = '';
    public $phone = '';
    public $selectedAddressId = null;
    
    public $addressLine1 = '';
    public $addressLine2 = '';
    public $city = '';
    public $province = '';
    public $postalCode = '';
    
    public $availableAddresses = array();

    public $selectedPaymentMethod = null;
    public $gcashReferenceNumber = '';
    public $bankTransferReferenceNumber = '';

    public $discountCode = '';
    public $appliedDiscount = null;

    public $showGcashReference = false;
    public $showBankTransferReference = false;
    public $paymentMethods = array();
    public $customer;
    public $isProcessing = false;

    protected $listeners = array('paymentMethodChanged');

    protected $rules = array(
        'firstName' => 'required|string|max:50',
        'lastName' => 'required|string|max:50',
        'phone' => 'required|string|max:15',
        'selectedAddressId' => 'required|exists:addresses,addressID',
        'selectedPaymentMethod' => 'required|exists:payment_methods,payment_methodID',
        'selectedShippingMethod' => 'required|in:JNT,LALAMOVE',
        'gcashReferenceNumber' => 'nullable|string|max:50',
        'bankTransferReferenceNumber' => 'nullable|string|max:50',
        'discountCode' => 'nullable|string|max:50',
    );

    protected $messages = array(
        'firstName.required' => 'First name is required.',
        'lastName.required' => 'Last name is required.',
        'phone.required' => 'Phone number is required.',
        'selectedAddressId.required' => 'Please select a delivery address.',
        'selectedPaymentMethod.required' => 'Please select a payment method.',
        'selectedShippingMethod.required' => 'Please select a shipping method.',
        'gcashReferenceNumber.required' => 'GCash reference number is required.',
        'bankTransferReferenceNumber.required' => 'Bank Transfer reference number is required.',
    );

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

        $this->cartItems = CartItem::where('customerID', $this->customer->customerID)
            ->with(array('product', 'variant'))
            ->get();

        if ($this->cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return redirect()->route('cart');
        }

        $this->cartCount = $this->cartItems->sum('quantity');
    }

    public function loadPaymentMethods()
    {
        // Only exclude standalone "Cash" but keep "Cash on Delivery"
        $excludedMethods = array(
            'Cash',
            'cash',
            'CASH'
        );
        
        $this->paymentMethods = PaymentMethod::where('is_active', true)
            ->whereNotIn('method_name', $excludedMethods)
            ->get();
        
        // Set default to Cash on Delivery if available, otherwise GCash
        $cashOnDelivery = $this->paymentMethods->where('method_name', 'Cash on Delivery')->first();
        $gcash = $this->paymentMethods->where('method_name', 'GCash')->first();
        
        if ($cashOnDelivery) {
            $this->selectedPaymentMethod = $cashOnDelivery->payment_methodID;
            // Cash on Delivery doesn't need reference number
            $this->showGcashReference = false;
            $this->showBankTransferReference = false;
        } elseif ($gcash) {
            $this->selectedPaymentMethod = $gcash->payment_methodID;
            $this->showGcashReference = true;
        } elseif ($this->paymentMethods->isNotEmpty()) {
            $firstMethod = $this->paymentMethods->first();
            $this->selectedPaymentMethod = $firstMethod->payment_methodID;
            // Check if it's bank transfer
            $methodName = strtolower($firstMethod->method_name);
            $this->showBankTransferReference = $methodName === 'bank transfer' || $methodName === 'banktransfer';
        }
    }

    public function loadShippingMethods()
    {
        $this->availableShippingMethods = array(
            'JNT' => array(
                'name' => 'J&T Express',
                'fee' => 70,
                'description' => '3-5 business days delivery',
            ),
            'LALAMOVE' => array(
                'name' => 'Lalamove',
                'fee' => 120,
                'description' => 'Same day delivery',
            ),
        );
        
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
        }
        
        // Clear reference numbers when switching payment methods
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
        $this->subtotal = $this->cartItems->sum('sub_total');
        
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
            $availableStock = $item->variant 
                ? $item->variant->stock_quantity 
                : $item->product->total_stock_quantity;

            if ($item->quantity > $availableStock) {
                throw new \Exception("Insufficient stock for " . $item->product->name . ". Available: " . $availableStock . ", Required: " . $item->quantity);
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
                $this->customer->update(array(
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'phone' => $this->phone,
                ));

                $selectedAddress = Address::find($this->selectedAddressId);
                
                $order = Order::create(array(
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
                ));

                foreach ($this->cartItems as $cartItem) {
                    OrderItem::create(array(
                        'orderID' => $order->orderID,
                        'productID' => $cartItem->productID,
                        'product_variant_id' => $cartItem->product_variant_id,
                        'size' => $cartItem->size,
                        'colorway' => $cartItem->colorway,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->unit_price,
                        'sub_total' => $cartItem->sub_total,
                    ));

                    if ($cartItem->variant) {
                        $cartItem->variant->decrement('stock_quantity', $cartItem->quantity);
                    } else {
                        $cartItem->product->decrement('total_stock_quantity', $cartItem->quantity);
                    }
                }

                $paymentStatus = 'Pending'; // All online payments are pending
                $referenceNumber = null;
                
                if ($this->showGcashReference && $this->gcashReferenceNumber) {
                    $referenceNumber = $this->gcashReferenceNumber;
                } elseif ($this->showBankTransferReference && $this->bankTransferReferenceNumber) {
                    $referenceNumber = $this->bankTransferReferenceNumber;
                }
                
                Payment::create(array(
                    'orderID' => $order->orderID,
                    'payment_methodID' => $this->selectedPaymentMethod,
                    'amount' => $this->totalAmount,
                    'reference_number' => $referenceNumber,
                    'status' => $paymentStatus,
                ));

                Shipping::create(array(
                    'orderID' => $order->orderID,
                    'shipping_method' => $this->selectedShippingMethod,
                    'shipping_status' => 'Pending',
                    'shipping_fee' => $this->deliveryFee,
                ));

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
        return view('livewire.checkout-page', array(
            'cartItems' => $this->cartItems,
            'subtotal' => $this->subtotal,
            'deliveryFee' => $this->deliveryFee,
            'discountAmount' => $this->discountAmount,
            'totalAmount' => $this->totalAmount,
            'cartCount' => $this->cartCount,
            'paymentMethods' => $this->paymentMethods,
            'availableAddresses' => $this->availableAddresses,
            'availableShippingMethods' => $this->availableShippingMethods,
            'showGcashReference' => $this->showGcashReference,
            'showBankTransferReference' => $this->showBankTransferReference,
        ));
    }
}