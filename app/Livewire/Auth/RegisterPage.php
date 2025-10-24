<?php

namespace App\Livewire\Auth;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

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

    // validation using livewire :<<
    public function register(){
       $this->validate([
    'firstname' => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
    'lastname'  => ['required', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
    'email'     => 'required|email|unique:users,email|max:255',
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



            $user = User::create([
                'name' => $this->firstname,
                'last_name' => $this->lastname,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => bcrypt($this->password),
                'is_active' => true,
            ]);

            $user->assignRole('customer'); //automatically assign the user as a customer
            
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

        return redirect()->route('login')->with('message', 'Account created successfully! Please login.');
    }


    

    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
