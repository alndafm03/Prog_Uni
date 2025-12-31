<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $guarded = ['id'];

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
    public function ownedapartment()
    {
        return $this->hasMany(apartment::class, 'owner_id');
    }
    public function booking()
    {
        return $this->hasMany(booking::class, 'renter_id');
    }
    public function bookedapartment()
    {
        return $this->hasManyThrough(
            Apartment::class,
            Booking::class,
            'renter_id', // Foreign key on bookings table
            'id', // Foreign key on apartments table
            'id', // Local key on users table
            'apartment_id' // Local key on bookings table
        );
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
