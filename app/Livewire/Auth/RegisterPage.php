<?php

namespace App\Livewire\Auth;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; // ✅ Added for API calls
use Illuminate\Support\Facades\Mail; // ✅ Added for sending emails
use Livewire\Component;
use Illuminate\Auth\Events\Registered; // ✅ Added for triggering Laravel’s built-in verification

class RegisterPage extends Component
{
    public $firstname;
    public $lastname;
    public $email;
    public $password;
    public $password_confirmation;

    public $phone;
    public $address_line_1;
    public $address_line_2;
    public $city;
    public $province;
    public $postal_code;

    // ✅ Email validation using MailboxLayer API
    public function checkEmailValidity($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $response = Http::get('https://apilayer.net/api/check', [
            'access_key' => env('MAILBOXLAYER_API_KEY'),
            'email' => $email,
            'smtp' => 1,
            'format' => 1,
        ]);

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        return $data['format_valid'] && $data['smtp_check'] && $data['mx_found'];
    }

    // validation using livewire :<<
    public function register()
    {
        $this->validate([
            'firstname' => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
            'lastname'  => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
            'email' => [
                'required',
                'email:rfc,dns', // checks format AND domain existence
                'unique:users,email',
                'max:255'
            ],
            'password'  => 'required|min:8|max:255|confirmed',
            'password_confirmation' => 'required',
            'postal_code' => 'required|numeric|digits_between:4,10',
            'address_line_1' => 'required|max:255',
            'address_line_2' => 'max:255',
            'city' => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
            'province' => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
            'phone' => [
                'required',
                'regex:/^[0-9]{10,11}$/',
            ],
        ]);

        // ✅ Check if email is real via API
        if (!$this->checkEmailValidity($this->email)) {
            $this->addError('email', 'The email address appears invalid or unreachable.');
            return;
        }

        // ✅ Continue registration as normal
        $user = User::create([
            'name' => $this->firstname,
            'last_name' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => bcrypt($this->password),
            'is_active' => true,
        ]);

        $user->assignRole('customer'); // automatically assign the user as a customer

        $customer = Customer::create([
            'user_id' => $user->id,
            'first_name' => $this->firstname,
            'last_name' => $this->lastname,
            'phone' => $this->phone,
            'is_active' => true,
        ]);

        $address = Address::create([
            'customerID' => $customer->customerID,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
        ]);

        // ✅ Trigger email verification (Laravel built-in)
        event(new Registered($user));

        // ✅ Optional: Send a welcome email immediately
        try {
            Mail::raw('Welcome to Flip Market! Your account has been successfully created.', function ($message) {
                $message->to($this->email)
                        ->subject('Welcome to Flip Market!');
            });
        } catch (\Exception $e) {
            // Silent fail — you can log this if needed
        }

        return redirect()->route('login')->with('message', 'Account created successfully! Please check your email to verify your account before logging in.');
    }

    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
