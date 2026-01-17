<?php

namespace App\Http\Controllers;

use App\Models\no;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\booking;

class OwnerController extends Controller
{
    public function checkAndUpdateStatus(Booking $booking)
    {
        if ($booking->status === 'approved' && now()->greaterThan($booking->end_date)) {
            $booking->status = 'completed';
            $booking->save();
        }
    }
    public function ownerbooking()
    {
        $bookings = booking::with(['apartment', 'user'])
            ->whereHas('apartment', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->status === 'approved' && now()->greaterThan($booking->end_date)) {
                $booking->status = 'completed';
                $booking->save();
            }
        }

        return response()->json([
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id'         => $booking->id,
                    'start_date' => $booking->start_date,
                    'end_date'   => $booking->end_date,
                    'status'     => $booking->status,
                    'apartment'  => [
                        'id'       => $booking->apartment->id,
                        'province' => $booking->apartment->province,
                        'city'     => $booking->apartment->city,
                        'price'    => $booking->apartment->price,
                        'address'  => $booking->apartment->address,
                    ],
                    'renter' => [
                        'id'         => $booking->user->id,
                        'first_name' => $booking->user->first_name,
                        'last_name'  => $booking->user->last_name,
                        'phone'      => $booking->user->phone,
                    ]
                ];
            })
        ]);
    }


    public function ownerbookingpending()
    {
        $user = Auth::user();

        if ($user->role === 'owner') {

            $bookings = Booking::with(['apartment.owner', 'user'])
                ->whereHas('apartment', function ($query) use ($user) {
                    $query->where('owner_id', $user->id);
                })
                ->where('status', 'pending')
                ->get();

            return response()->json([
                'bookings' => $bookings->map(function ($booking) {
                    return [
                        'id'         => $booking->id,
                        'start_date' => $booking->start_date,
                        'end_date'   => $booking->end_date,
                        'status'     => $booking->status,

                        'apartment'  => [
                            'id'       => $booking->apartment->id,
                            'province' => $booking->apartment->province,
                            'city'     => $booking->apartment->city,
                            'price'    => $booking->apartment->price,
                            'address'  => $booking->apartment->address,
                        ],

                        // معلومات المستأجر
                        'renter' => [
                            'id'         => $booking->user->id,
                            'first_name' => $booking->user->first_name,
                            'last_name'  => $booking->user->last_name,
                            'phone'      => $booking->user->phone,
                        ]
                    ];
                })
            ]);
        }

        if ($user->role === 'renter') {

            $bookings = Booking::with(['apartment.owner'])
                ->where('renter_id', $user->id)
                ->where('status', 'pending')
                ->get();

            return response()->json([
                'bookings' => $bookings->map(function ($booking) {
                    return [
                        'id'         => $booking->id,
                        'start_date' => $booking->start_date,
                        'end_date'   => $booking->end_date,
                        'status'     => $booking->status,

                        'apartment'  => [
                            'id'       => $booking->apartment->id,
                            'province' => $booking->apartment->province,
                            'city'     => $booking->apartment->city,
                            'price'    => $booking->apartment->price,
                            'address'  => $booking->apartment->address,
                        ],

                        'owner' => [
                            'id'         => $booking->apartment->owner->id,
                            'first_name' => $booking->apartment->owner->first_name,
                            'last_name'  => $booking->apartment->owner->last_name,
                            'phone'      => $booking->apartment->owner->phone,
                        ]
                    ];
                })
            ]);
        }

        return response()->json(['message' => 'Role not supported'], 403);
    }

    public function approve($id)
    {
        $booking = Booking::with('apartment')->find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->apartment->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'You are not authorized to approve this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'This booking cannot be approved because it is not in pending status'
            ], 400);
        }
        $booking->status = 'approved';
        $booking->save();

        return response()->json([
            'message' => 'Booking approved successfully',
            'booking' => $booking
        ]);
    }

    public function reject($id)
    {
        $booking = Booking::with('apartment')->find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->apartment->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'You are not authorized to reject this booking'
            ], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'This booking cannot be rejected because it is not in pending status'
            ], 400);
        }

        $booking->status = 'rejected';
        $booking->save();

        return response()->json([
            'message' => 'Booking rejected successfully',
            'booking' => $booking
        ]);
    }
}
