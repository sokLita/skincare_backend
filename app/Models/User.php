<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'phone',
        'address',
        'profile_photo',
        'avatar',
        'provider',
        'provider_id',
'google_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function wishlist() {
        return $this->hasMany(Wishlist::class);
    }

    public function cart() {
        return $this->hasMany(Cart::class);
    }
}
