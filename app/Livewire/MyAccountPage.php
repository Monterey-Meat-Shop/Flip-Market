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
                'user_id'    => Auth::id(),
                'first_name' => Auth::user()->name ?? '',
                'last_name'  => '',
                'email'      => Auth::user()->email,
            ]);
        }

        $this->loadProfile();
        $this->loadAddresses();
    }

    public function loadProfile()
    {
        $user = Auth::user();

        // ✅ Prefer data from users table; fall back to customer if needed
        $this->firstname = $user->name
            ?? ($this->customer->first_name ?? '');

        $this->lastname = $user->last_name
            ?? ($this->customer->last_name ?? '');

        $this->email = $user->email
            ?? ($this->customer->email ?? '');

        $this->phone = $user->phone
            ?? ($this->customer->phone ?? '');
    }

    public function loadAddresses()
    {
        $this->addresses = $this->customer->address()->get();
    }

    public function saveProfile()
    {
        $this->validate([
            'firstname' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s\-]+$/',
                'not_regex:/[0-9!@#$%^&*(),.?":{}|<>]/'
            ],
            'lastname' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z\s\-]+$/',
                'not_regex:/[0-9!@#$%^&*(),.?":{}|<>]/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'phone' => [
                'nullable',
                'regex:/^[0-9]{11}$/'
            ],
        ], [
            'firstname.regex'      => 'The first name may only contain letters, spaces, or hyphens.',
            'lastname.regex'       => 'The last name may only contain letters, spaces, or hyphens.',
            'firstname.not_regex'  => 'The first name cannot contain symbols or numbers.',
            'lastname.not_regex'   => 'The last name cannot contain symbols or numbers.',
            'phone.regex'          => 'The phone number must be exactly 11 digits and contain numbers only.',
            'email.email'          => 'Please enter a valid and active email address.',
        ]);

        try {
            // ✅ Update customer record
            $this->customer->update([
                'first_name' => $this->firstname,
                'last_name'  => $this->lastname,
                'email'      => $this->email,
                'phone'      => $this->phone,
            ]);

            // ✅ Update auth user: keep FIRST and LAST name separate
            $user = Auth::user();

            $user->update([
                'name'      => $this->firstname,   // first name only
                'last_name' => $this->lastname,
                'email'     => $this->email,
                'phone'     => $this->phone,
            ]);

            // ✅ Refresh session user
            Auth::setUser($user->fresh());

            // ✅ Notify navbar / frontend (kept from your logic)
            $updatedName = "{$this->firstname} {$this->lastname}";
            $this->dispatch('userNameUpdated', $updatedName);

            // ✅ Toast
            $this->dispatch('notify', [
                'message' => 'Profile updated successfully!',
                'type'    => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Failed to update profile: ' . $e->getMessage(),
                'type'    => 'error',
            ]);
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
            $this->city           = $address->city;
            $this->province       = $address->province;
            $this->postal_code    = $address->postal_code;
            $this->showAddressForm = true;
        }
    }

    public function saveAddress()
    {
        $this->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'province'       => 'required|string|max:100',
            'postal_code'    => 'required|string|max:20',
        ]);

        try {
            $addressData = [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city'           => $this->city,
                'province'       => $this->province,
                'postal_code'    => $this->postal_code,
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
        $this->city           = '';
        $this->province       = '';
        $this->postal_code    = '';
        $this->editingAddressId = null;
    }

    public function changePassword()
    {
        $this->validate([
            'current_password'  => 'required',
            'new_password'      => 'required|min:8',
            'confirm_password'  => 'required|same:new_password',
        ]);

        try {
            $user = Auth::user();

            if (! Hash::check($this->current_password, $user->password)) {
                session()->flash('error', 'Current password is incorrect.');

                //  Also trigger toast (this was missing!)
                $this->dispatch('notify', [
                    'message' => 'Current password is incorrect.',
                    'type'    => 'error',
                ]);

                return;
            }

            $user->update([
                'password' => Hash::make($this->new_password),
            ]);

            $this->current_password = '';
            $this->new_password     = '';
            $this->confirm_password = '';

            session()->flash('success', 'Password changed successfully!');

            //  Notify toast
            $this->dispatch('notify', [
                'message' => 'Password changed successfully!',
                'type'    => 'success',
            ]);

            //  NEW: Dispatch event to switch back to profile tab
            $this->dispatch('password-changed-success');

        } catch (\Exception $e) {

            session()->flash('error', 'Failed to change password: ' . $e->getMessage());

            //  Notify toast
            $this->dispatch('notify', [
                'message' => 'Failed to change password: ' . $e->getMessage(),
                'type'    => 'error',
            ]);
        }
    }


    public function render()
    {
        return view('livewire.my-account-page');
    }
}