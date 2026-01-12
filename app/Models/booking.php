<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class booking extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
    public function apartment()
    {
        return $this->belongsTo(apartment::class);
    }
    public function review()
    {
        return $this->hasOne(review::class, 'booking_id');
    }
    public function messages()
    {
        return $this->hasMany(message::class);
    }
}
