<?php

namespace App\Http\Controllers;

use App\Models\no;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\booking;

class OwnerController extends Controller
{

    //تشيك على انتهاء مدة الحجز
    public function checkAndUpdateStatus(Booking $booking)
    {
        if ($booking->status === 'approved' && now()->greaterThan($booking->end_date)) {
            $booking->status = 'completed';
            $booking->save();
        }
    }
    public function ownerBooking()
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

        // إذا كان المستخدم Owner → رجّع حجوزات شققه
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

        // إذا كان المستخدم Renter → رجّع حجوزاته pending مع معلومات المالك
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

                        // معلومات المالك
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
        // Get booking with apartment details
        $booking = Booking::with('apartment')->find($id);

        // Check if booking exists
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        // Check if the current user is the owner of the apartment
        if ($booking->apartment->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'You are not authorized to approve this booking'
            ], 403);
        }

        // Ensure the current status is pending
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'This booking cannot be approved because it is not in pending status'
            ], 400);
        }
        // Update status to approved
        $booking->status = 'approved';
        $booking->save();

        return response()->json([
            'message' => 'Booking approved successfully',
            'booking' => $booking
        ]);
    }

    public function reject($id)
    {
        // Get booking with apartment details
        $booking = Booking::with('apartment')->find($id);

        // Check if booking exists
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        // Check if the current user is the owner of the apartment
        if ($booking->apartment->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'You are not authorized to reject this booking'
            ], 403);
        }

        // Ensure the current status is pending
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'This booking cannot be rejected because it is not in pending status'
            ], 400);
        }

        // Update status to rejected
        $booking->status = 'rejected';
        $booking->save();

        return response()->json([
            'message' => 'Booking rejected successfully',
            'booking' => $booking
        ]);
    }
}
