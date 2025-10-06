<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MyAccountPage extends Component
{
    // Profile fields
    public $firstname = '';
    public $lastname = '';
    public $email = '';
    public $phone = '';
    
    // Address fields
    public $address_line_1 = '';
    public $address_line_2 = '';
    public $city = '';
    public $province = '';
    public $postal_code = '';
    
    // Password fields
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';
    
    public $customer;

    protected $rules = [
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address_line_1' => 'nullable|string|max:255',
        'address_line_2' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:100',
        'postal_code' => 'nullable|string|max:20',
    ];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$this->customer) {
            // Create customer record if it doesn't exist
            $this->customer = Customer::create([
                'user_id' => Auth::id(),
                'first_name' => Auth::user()->name ?? '',
                'last_name' => '',
                'email' => Auth::user()->email,
            ]);
        }

        // Load profile data
        $this->firstname = $this->customer->first_name ?? '';
        $this->lastname = $this->customer->last_name ?? '';
        $this->email = $this->customer->email ?? Auth::user()->email;
        $this->phone = $this->customer->phone ?? '';

        // Load address data (get the first address if exists)
        $address = $this->customer->address()->first();
        if ($address) {
            $this->address_line_1 = $address->address_line_1;
            $this->address_line_2 = $address->address_line_2;
            $this->city = $address->city;
            $this->province = $address->province;
            $this->postal_code = $address->postal_code;
        }
    }

    public function saveProfile()
    {
        $this->validate();

        try {
            // Update customer information
            $this->customer->update([
                'first_name' => $this->firstname,
                'last_name' => $this->lastname,
                'email' => $this->email,
                'phone' => $this->phone,
            ]);

            // Update or create address
            $existingAddress = $this->customer->address()->first();
            
            $addressData = [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
            ];

            if ($existingAddress) {
                $existingAddress->update($addressData);
            } else {
                $this->customer->address()->create($addressData);
            }

            session()->flash('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        try {
            $user = Auth::user();

            // Verify current password
            if (!Hash::check($this->current_password, $user->password)) {
                session()->flash('error', 'Current password is incorrect.');
                return;
            }

            // Update password
            $user->update([
                'password' => Hash::make($this->new_password)
            ]);

            // Clear password fields
            $this->current_password = '';
            $this->new_password = '';
            $this->confirm_password = '';

            session()->flash('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to change password: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.my-account-page');
    }
}