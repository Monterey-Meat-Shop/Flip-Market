<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Main extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.main', [
            'products' => Product::paginate(5)
        ]);
    }
}
