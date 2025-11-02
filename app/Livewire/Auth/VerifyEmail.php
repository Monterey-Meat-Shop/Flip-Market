<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class VerifyEmail extends Component
{
    public function resendVerification()
    {
        if (Auth::user() && !Auth::user()->hasVerifiedEmail()) {
            Auth::user()->sendEmailVerificationNotification();
            session()->flash('status', 'verification-link-sent');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
