<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class review extends Model
{
    protected $guarded = ['id'];

    public function booking()
    {
        return $this->belongsTo(booking::class, 'booking_id');
    }
}
