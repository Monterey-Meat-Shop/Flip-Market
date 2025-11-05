<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoritePage extends Component
{
    public $favorites = [];

    public function mount()
    {
        // Check if user is logged in
        if (Auth::check()) {
            $customer = Auth::user()->customer; // Assuming User hasOne Customer
            if ($customer) {
                $this->favorites = Favorite::with('product')
                    ->where('customerID', $customer->customerID)
                    ->get();
            }
        }
    }

    public function removeFavorite($favoriteId)
    {
        $favorite = Favorite::where('favoriteID', $favoriteId)->first();
        if ($favorite) {
            $favorite->delete();
            $this->favorites = $this->favorites->where('favoriteID', '!=', $favoriteId);
            session()->flash('message', 'Removed from favorites.');
        }
    }

    public function render()
    {
        return view('livewire.favorite-page', [
            'favorites' => $this->favorites,
        ]);
    }
}
