<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class HeaderUserName extends Component
{
    public $name;

    protected $listeners = ['userNameUpdated' => 'updateName'];

    public function mount()
    {
        $this->name = Auth::user()->customer
            ? trim(Auth::user()->customer->first_name . ' ' . Auth::user()->customer->last_name)
            : Auth::user()->name;
    }

    public function updateName($newName)
    {
        $this->name = $newName;
    }

    public function render()
    {
        return view('livewire.header-user-name');
    }
}
