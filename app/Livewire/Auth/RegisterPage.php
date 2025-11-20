<?php

namespace App\Livewire\Auth;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Illuminate\Auth\Events\Registered;
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

    public $successMessage = null;
    public $emailError = null;   //real-time email error message

    //REAL-TIME EMAIL CHECK
    public function updatedEmail()
    {
        if (!$this->email) {
            $this->emailError = null;
            return;
        }

        if (!$this->checkEmailValidity($this->email)) {
            $this->emailError = "❌ This email is invalid, unreachable, or disposable.";
        } else {
            $this->emailError = null;
        }
    }

    //STRICT EMAIL VALIDATION
    public function checkEmailValidity($email)
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $apiKey = env('MAILBOXLAYER_API_KEY');

        if (empty($apiKey)) {
            return false; 
        }

        try {
            $response = Http::timeout(5)->get('https://apilayer.net/api/check', [
                'access_key' => $apiKey,
                'email' => $email,
                'smtp' => 1,
                'format' => 1,
            ]);
        } catch (\Throwable $e) {
            return false;
        }

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        if (!is_array($data) || empty($data)) {
            return false;
        }

        if (isset($data['success']) && $data['success'] === false) {
            return false;
        }

        //STRICT: require ALL to be true
        return 
            !empty($data['format_valid']) &&
            !empty($data['smtp_check']) &&
            !empty($data['mx_found']) &&
            empty($data['disposable']); // block temporary emails
    }

    public function register()
    {

        $dataPath = public_path('data/philippines.json');
        $locations = [];
        if (file_exists($dataPath)) {
            $locations = json_decode(file_get_contents($dataPath), true) ?: [];
        }
        $allowedProvinces = array_keys($locations);
        $allowedCities = $locations[$this->province] ?? [];

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
        'email:rfc,dns',
        'unique:users,email',
        'max:255'
    ],

    'phone' => [
        'required',
        'regex:/^[0-9]{11}$/',
        'unique:users,phone'
    ],

    'password'  => 'required|min:8|max:255|confirmed',
    'password_confirmation' => 'required',

    'postal_code' => 'required|numeric|digits_between:4,10',
    'address_line_1' => 'required|max:255',
    'address_line_2' => 'max:255',

    'city' => $cityRules,
    'province' => $provinceRules,
]);


        //BLOCK INVALID EMAILS STRICTLY

        if (!$this->checkEmailValidity($this->email)) {
            $this->addError('email', 'The email address is invalid, unreachable, or disposable.');
            return;
        }

        
        $user = User::create([
            'name'          => $this->firstname,
            'last_name'     => $this->lastname,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'password'      => bcrypt($this->password),
            'is_active'     => true,
            'postal_code'     => $this->postal_code,
            'address_line_1'  => $this->address_line_1,
            'address_line_2'  => $this->address_line_2,
            'city'            => $this->city,
            'province'        => $this->province,
        ]);

        $user->assignRole('customer');

        $customer = Customer::create([
            'user_id'    => $user->id,
            'first_name' => $this->firstname,
            'last_name'  => $this->lastname,
            'phone'      => $this->phone,
            'is_active'  => true,
        ]);

        Address::create([
            'customerID'     => $customer->customerID,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city'           => $this->city,
            'province'       => $this->province,
            'postal_code'    => $this->postal_code,
        ]);

        event(new Registered($user));

        try {
            Mail::raw(
                'Welcome to Flip Market! Your account has been successfully created.',
                function ($message) {
                    $message->to($this->email)
                            ->subject('Welcome to Flip Market!');
                }
            );
        } catch (\Exception $e) {}

        $this->successMessage =
            '🎉 Account created successfully! A verification and welcome email were sent to ' .
            $this->email . '. Please check your inbox.';

        $this->reset([
            'firstname',
            'lastname',
            'email',
            'password',
            'password_confirmation',
            'phone',
            'address_line_1',
            'address_line_2',
            'city',
            'province',
            'postal_code',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
