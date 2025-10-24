<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ResetPassword extends Component
{
    public $token;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email'); // auto-fill email from reset link
    }

    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email:rfc,dns|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.exists' => 'We could not find an account with that email address.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('success', 'Your password has been reset successfully! You may now log in.');
            return redirect()->route('login');
        } else {
            session()->flash('error', 'Invalid or expired token. Please request a new reset link.');
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
