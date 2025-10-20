<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CartCounter extends Component
{
    public $cartCount = 0;

    protected $listeners = ['cartUpdated' => 'updateCartCount'];

    public function mount()
    {
        $this->updateCartCount();
    }

    public function updateCartCount()
    {
        if (!Auth::check()) {
            $this->cartCount = 0;
            return;
        }

        $customer = Customer::where('user_id', Auth::id())->first();

        if ($customer) {
            $this->cartCount = CartItem::where('customerID', $customer->customerID)
                ->sum('quantity');
        } else {
            $this->cartCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}