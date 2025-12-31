<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
{
    $data = [
        'users' => [
            'total'      => User::count(),
            'owners'     => User::where('role', 'owner')->count(),
            'renters'    => User::where('role', 'renter')->count(),
            'pending'    => User::where('status', 'pending')->count(),
            'rejected'   => User::where('status', 'rejected')->count(),
        ],
        'apartments' => [
            'total' => Apartment::count(),
        ],
        'bookings' => [
            'total'     => Booking::count(),
            'pending'   => Booking::where('status', 'pending')->count(),
            'approved'  => Booking::where('status', 'approved')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'rejected'  => Booking::where('status', 'rejected')->count(),
        ],
        'reviews' => [
            'total'   => Review::count(),
            'average' => round(Review::avg('rating'), 2),
        ]
    ];

    return response()->json($data);
}
}
