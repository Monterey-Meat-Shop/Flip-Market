<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use Livewire\Component;

class Landingpage extends Component
{
    public function render()
    {

        $brands = Brand::where('is_active', operator: 1)->get();
        return view('livewire.landingpage', [
            'brands' => $brands,
        
        ]);
    }
}
