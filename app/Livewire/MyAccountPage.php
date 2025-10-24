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
    
    // Address fields for new/edit
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
    public $addresses;
    public $editingAddressId = null;
    public $showAddressForm = false;

    protected $rules = [
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address_line_1' => 'required|string|max:255',
        'address_line_2' => 'nullable|string|max:255',
        'city' => 'required|string|max:100',
        'province' => 'required|string|max:100',
        'postal_code' => 'required|string|max:20',
    ];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->customer = Customer::where('user_id', Auth::id())->first();
        
        if (!$this->customer) {
            $this->customer = Customer::create([
                'user_id' => Auth::id(),
                'first_name' => Auth::user()->name ?? '',
                'last_name' => '',
                'email' => Auth::user()->email,
            ]);
        }

        $this->loadProfile();
        $this->loadAddresses();
    }

    public function loadProfile()
    {
        $this->firstname = $this->customer->first_name ?? '';
        $this->lastname = $this->customer->last_name ?? '';
        $this->email = $this->customer->email ?? Auth::user()->email;
        $this->phone = $this->customer->phone ?? '';
    }

    public function loadAddresses()
    {
        $this->addresses = $this->customer->address()->get();
    }

    public function saveProfile()
{
    $this->validate([
        'firstname' => 'required|string|max:255',
        'lastname'  => 'required|string|max:255',
        'email'     => 'required|email|max:255',
        'phone'     => 'nullable|string|max:20',
    ]);

 try {
    $this->customer->update([
        'first_name' => $this->firstname,
        'last_name'  => $this->lastname,
        'email'      => $this->email,
        'phone'      => $this->phone,
    ]);

    // Update Auth user’s name field (optional, keeps it consistent)
    $fullName = $this->firstname . ' ' . $this->lastname;
    Auth::user()->update(['name' => $fullName]);

    // 🔥 Dispatch Livewire event (v3 syntax)
    $this->dispatch('userNameUpdated', $fullName);

    session()->flash('success', 'Profile updated successfully!');
} catch (\Exception $e) {
    session()->flash('error', 'Failed to update profile: ' . $e->getMessage());
}

}


    public function showNewAddressForm()
    {
        $this->resetAddressForm();
        $this->showAddressForm = true;
        $this->editingAddressId = null;
    }

    public function editAddress($addressId)
    {
        $address = Address::find($addressId);
        if ($address && $address->customerID === $this->customer->customerID) {
            $this->editingAddressId = $addressId;
            $this->address_line_1 = $address->address_line_1;
            $this->address_line_2 = $address->address_line_2;
            $this->city = $address->city;
            $this->province = $address->province;
            $this->postal_code = $address->postal_code;
            $this->showAddressForm = true;
        }
    }

    public function saveAddress()
    {
        $this->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
        ]);

        try {
            $addressData = [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city' => $this->city,
                'province' => $this->province,
                'postal_code' => $this->postal_code,
            ];

            if ($this->editingAddressId) {
                $address = Address::find($this->editingAddressId);
                if ($address && $address->customerID === $this->customer->customerID) {
                    $address->update($addressData);
                    session()->flash('success', 'Address updated successfully!');
                }
            } else {
                $this->customer->address()->create($addressData);
                session()->flash('success', 'Address added successfully!');
            }

            $this->loadAddresses();
            $this->resetAddressForm();
            $this->showAddressForm = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save address: ' . $e->getMessage());
        }
    }

    public function deleteAddress($addressId)
    {
        try {
            $address = Address::find($addressId);
            if ($address && $address->customerID === $this->customer->customerID) {
                $address->delete();
                $this->loadAddresses();
                session()->flash('success', 'Address deleted successfully!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete address: ' . $e->getMessage());
        }
    }

    public function cancelAddressForm()
    {
        $this->showAddressForm = false;
        $this->resetAddressForm();
    }

    public function resetAddressForm()
    {
        $this->address_line_1 = '';
        $this->address_line_2 = '';
        $this->city = '';
        $this->province = '';
        $this->postal_code = '';
        $this->editingAddressId = null;
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

            if (!Hash::check($this->current_password, $user->password)) {
                session()->flash('error', 'Current password is incorrect.');
                return;
            }

            $user->update([
                'password' => Hash::make($this->new_password)
            ]);

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