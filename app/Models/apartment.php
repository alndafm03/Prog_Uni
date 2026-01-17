<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class apartment extends Model
{
    protected $guarded = ['id', 'is_available'];
    use HasFactory;
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }


    public function booking()
    {
        return $this->hasMany(booking::class);
    }
    public function renters()
    {
        return $this->hasManyThrough(
            User::class,
            booking::class,
            'apartment_id',
            'id',
            'id',
            'renter_id'
        );
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
