<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ Added to enable email verification
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Customer;
use App\Models\Order;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail // ✅ Added MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        //'first_name',
        'last_name',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Only allow active (non-deleted) users with specific roles
        if ($this->trashed()) {
            return false;
        }

        return $this->hasAnyRole(['admin', 'manager', 'cashier']);
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function orders()
    {
        return $this->hasManyThrough(
            Order::class,     
            Customer::class,  
            'user_id',     
            'customerID',  
            'id',          
            'customerID'
        );
    }
}
