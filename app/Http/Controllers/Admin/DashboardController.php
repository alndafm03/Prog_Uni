<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\apartment;
use App\Models\booking;
use App\Models\review;

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
            'total' => apartment::count(),
        ],
        'bookings' => [
            'total'     => booking::count(),
            'pending'   => booking::where('status', 'pending')->count(),
            'approved'  => booking::where('status', 'approved')->count(),
            'completed' => booking::where('status', 'completed')->count(),
            'rejected'  => booking::where('status', 'rejected')->count(),
        ],
        'reviews' => [
            'total'   => review::count(),
            'average' => round(review::avg('rating'), 2),
        ]
    ];

    return response()->json($data);
}
}
