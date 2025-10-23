<?php

namespace App\Livewire;

use Livewire\Component;

class ReturnConfirmationPage extends Component
{
    public function render()
    {
        return view('livewire.return-confirmation-page')
            ->layout('layouts.app');
    }
}