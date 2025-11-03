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
use Illuminate\Validation\Rule;

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

    // ✅ NEW: Success message property
    public $successMessage = null;

    // ✅ Email validation using MailboxLayer API (safe/fail-open)
    public function checkEmailValidity($email)
    {
        // quick format check first
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $apiKey = env('MAILBOXLAYER_API_KEY');

        // if no API key configured, skip external check (do not block)
        if (empty($apiKey)) {
            return true;
        }

        try {
            $response = Http::timeout(5)->get('https://apilayer.net/api/check', [
                'access_key' => $apiKey,
                'email' => $email,
                'smtp' => 1,
                'format' => 1,
            ]);
        } catch (\Throwable $e) {
            // API request failed (network, timeout, etc.) — do not block registration
            return true;
        }

        if ($response->failed()) {
            // upstream service error — allow registration to proceed
            return true;
        }

        $data = $response->json();

        if (!is_array($data) || empty($data)) {
            // unexpected payload — don't block user
            return true;
        }

        // If provider returned an error payload, allow registration (avoid false negatives)
        if (isset($data['success']) && $data['success'] === false) {
            return true;
        }

        // Only use checks that exist in the response. If none exist, allow registration.
        $hasFormat = array_key_exists('format_valid', $data);
        $hasSmtp   = array_key_exists('smtp_check', $data);
        $hasMx     = array_key_exists('mx_found', $data);

        if (! ($hasFormat || $hasSmtp || $hasMx)) {
            return true;
        }

        $formatValid = $hasFormat ? (bool) $data['format_valid'] : true;
        $smtpCheck   = $hasSmtp   ? (bool) $data['smtp_check']   : true;
        $mxFound     = $hasMx     ? (bool) $data['mx_found']     : true;

        // require all available checks to be truthy
        return $formatValid && $smtpCheck && $mxFound;
    }

    // validation using livewire :<<
    public function register()
    {
        // Load provinces/cities dataset
        $dataPath = public_path('data/philippines.json');
        $locations = [];
        if (file_exists($dataPath)) {
            $locations = json_decode(file_get_contents($dataPath), true) ?: [];
        }
        $allowedProvinces = array_keys($locations);
        $allowedCities = $locations[$this->province] ?? [];

        // Dynamic rules: prefer exact match against dataset; fallback to relaxed pattern
        $provinceRules = ['required', 'max:255'];
        if (!empty($allowedProvinces)) {
            $provinceRules[] = Rule::in($allowedProvinces);
        } else {
            $provinceRules[] = 'regex:/^[A-Za-z0-9\s\-\.\'\(\)&]+$/';
        }

        $cityRules = ['required', 'max:255'];
        if (!empty($allowedCities)) {
            $cityRules[] = Rule::in($allowedCities);
        } else {
            $cityRules[] = 'regex:/^[A-Za-z0-9\s\-\.\'\(\)&]+$/';
        }

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
            'city' => $cityRules,
            'province' => $provinceRules,
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

        // ✅ Show success message instead of redirect
        $this->successMessage = '🎉 Account created successfully! A verification and welcome email were sent to ' . $this->email . '. Please check your inbox.';

        // Optional: clear form inputs
        $this->reset(['firstname','lastname','email','password','password_confirmation','phone','address_line_1','address_line_2','city','province','postal_code']);
    }

    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
