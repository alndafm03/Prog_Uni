<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\booking;
use App\Models\message;

class MessagesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'message' => 'required|string',
        ]);

        $booking = booking::with('apartment')->find($request->booking_id);

        $ownerId = $booking->apartment->owner_id;
        $renterId = $booking->renter_id;

        if (!in_array(Auth::id(), [$ownerId, $renterId])) {
            return response()->json(['message' => 'غير مصرح لك بإرسال رسالة لهذا الحجز'], 403);
        }

        $message = message::create([
            'booking_id' => $booking->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'تم إرسال الرسالة', 'data' => $message]);
    }
    public function index($booking_id)
    {
        $booking = booking::with('apartment')->findOrFail($booking_id);
        $ownerId = $booking->apartment->owner_id;
        $renterId = $booking->renter_id;

        if (!in_array(Auth::id(), [$ownerId, $renterId])) {
            return response()->json(['message' => 'غير مصرح لك بعرض الرسائل'], 403);
        }

        $messages = message::where('booking_id', $booking_id)
            ->select('id', 'sender_id', 'message', 'created_at') // فقط المهم
            ->with(['sender:id,first_name,last_name']) // فقط الاسم
            ->orderBy('created_at')->get();

        return response()->json(['messages' => $messages]);
    }
    public function inbox()
    {
        $userId = Auth::id();

        $bookings = booking::where(function ($q) use ($userId) {
            $q->where('renter_id', $userId)
                ->orWhereHas('apartment', function ($q2) use ($userId) {
                    $q2->where('owner_id', $userId);
                });
        })
            ->with([
                'apartment:id,owner_id,province,city,address,price',
                'apartment.owner:id,first_name,last_name,phone',
                'user:id,first_name,last_name,phone', // ← بدل renter
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->get();

        return response()->json(['chats' => $bookings]);
    }
}
