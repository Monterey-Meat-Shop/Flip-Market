<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;

class ForgotPassword extends Component
{
    public $email;

    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email:rfc,dns|exists:users,email',
        ], [
            'email.exists' => 'We can’t find a user with that email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Password reset link sent! Please check your email inbox.');
        } else {
            session()->flash('error', 'Failed to send reset link. Try again later.');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
