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
        // جلب كل الحجوزات المرتبطة بشقق يملكها المستخدم الحالي وحالتها pending فقط
        $bookings = Booking::with(['apartment', 'user'])
            ->whereHas('apartment', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->where('status', 'pending') // ← الشرط الجديد
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
        // Prevent approving past bookings
        if ($booking->start_date < now()->toDateString()) {
            return response()->json(['message' => 'Cannot approve a booking with a past date'], 400);
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
        if ($booking->start_date < now()->toDateString()) {
            return response()->json(['message' => 'Cannot approve a booking with a past date'], 400);
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
