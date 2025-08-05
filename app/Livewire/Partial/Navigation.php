<?php

namespace App\Livewire\Partial;

use Livewire\Component;

class Navigation extends Component
{   
    public $isAuthenticated = false;

    public function render()
    {
        return view('livewire.partial.navigation');
    }
}
