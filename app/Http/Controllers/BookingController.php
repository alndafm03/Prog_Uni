<?php

namespace App\Http\Controllers;

use App\Models\booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function renterBooking()
    {
        // جلب كل الحجوزات الخاصة بالمستأجر الحالي
        $bookings = Booking::with(['apartment.owner'])
            ->where('renter_id', Auth::id())
            ->get();

        // تحديث الحالة إذا انتهى زمن الاستئجار
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


    // إنشاء حجز جديد
    public function store(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        // Check if the authenticated user is renter
        if (Auth::user()->role !== 'renter') {
            return response()->json(['message' => 'Only renter accounts can create reservations'], 403);
        }
        // if ($request->start_date < now()->toDateString()) {
        //     return response()->json(['message' => 'Start date cannot be in the past'], 400);
        // }

        $exists = booking::where('apartment_id', $request->apartment_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'هذه الفترة محجوزة بالفعل'], 409);
        }

        $renter_id = Auth::user()->id;


        $reservation = booking::create([


            'renter_id'       => $renter_id,
            'apartment_id' => $request->apartment_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'status'        => 'pending',
        ]);
        if ($reservation->status == 'pending')
            return response()->json(['message' => 'تم إنشاء الحجز بنجاح, بانتظار الموافقة ']);
        if ($reservation->status == 'approved') {
            $reservation->save();
            return response()->json(['message' => ' تمت الموافقة على الحجز', 'reservation' => $reservation], 201);
        }
        if ($reservation->status == 'rejected') {
            $reservation->delete();
            return response()->json(['message' => ' تم رفض الحجز', 'reservation' => $reservation], 400);
        }
    }

    public function show($id)
    {
        $booking = Booking::with(['apartment.owner:id,first_name,last_name,phone'])
            ->findOrFail($id);

        return response()->json([
            'booking' => [
                'id'         => $booking->id,
                'start_date' => $booking->start_date,
                'end_date'   => $booking->end_date,
                'status'     => $booking->status,
            ],
            'apartment' => [
                'id'       => $booking->apartment->id,
                'province' => $booking->apartment->province,   // بدل الـ title
                'city'     => $booking->apartment->city,       // إضافة city
                'price'    => $booking->apartment->price,      // السعر من الشقة
                'address'  => $booking->apartment->address,
            ],
            'owner' => [
                'first_name' => $booking->apartment->owner->first_name,
                'last_name'  => $booking->apartment->owner->last_name,
                'phone'      => $booking->apartment->owner->phone,
            ]
        ]);
    }


    // تعديل الحجز من قبل المستخدم
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $reservation = booking::where('id', $id)
            ->where('renter_id', Auth::id())
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'الحجز غير موجود أو لا يخصك'], 404);
        }

        $reservation->update([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'pending'
        ]);

        return response()->json([
            'message' => 'تم تحديث الحجز بنجاح',
            'reservation' => $reservation
        ]);
    }

    // حذف الحجز
    public function destroy($id)
    {
        $reservation = booking::where('id', $id)
            ->where('renter_id', Auth::id())
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'الحجز غير موجود أو لا يخصك'], 404);
        }

        $reservation->delete();

        return response()->json(['message' => 'تم حذف الحجز بنجاح']);
    }
}
