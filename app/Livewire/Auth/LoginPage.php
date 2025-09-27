<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginPage extends Component
{
    public $email;
    public $password;
    public $remember = false;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            $user = Auth::user();

            // Check role - only allow "customer"
            if ($user->hasRole('customer')) {
                return redirect()->route('landingpage');
            }

            Auth::logout();
            return back()->withErrors(['email' => 'You are not allowed to login here.']);
        }

        $this->addError('email', 'Invalid login credentials.');
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
