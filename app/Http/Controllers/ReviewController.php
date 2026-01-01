<?php

namespace App\Http\Controllers;

use App\Models\review;
use App\Models\booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // عرض كل المراجعات
    public function index()
    {
        $reviews = review::with('booking')->get();
        return response()->json($reviews);
    }

    // عرض مراجعات مرتبطة بحجز معين
    public function showByBooking($booking_id)
    {
        $reviews = review::where('booking_id', $booking_id)
                         ->with('booking')
                         ->get();

        return response()->json($reviews);
    }

    // إنشاء مراجعة جديدة
    public function store(Request $request, $booking_id)
    {
        $request->validate([
            'rating'  => 'required|in:1,2,3,4,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = booking::findOrFail($booking_id);

        // تأكد أن المستخدم هو صاحب الحجز
        if ($booking->renter_id !== Auth::id()) {
            return response()->json(['message' => 'لا يمكنك إضافة مراجعة لحجز لا يخصك'], 403);
        }

        $review = review::create([
            'booking_id' => $booking->id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return response()->json([
            'message' => 'تم إضافة المراجعة بنجاح',
            'review'  => $review
        ], 201);
    }

    // تعديل مراجعة
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|in:1,2,3,4,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = review::findOrFail($id);

        // تأكد أن المراجعة مرتبطة بحجز يخص المستخدم
        if ($review->booking->renter_id !== Auth::id()) {
            return response()->json(['message' => 'لا يمكنك تعديل مراجعة لا تخصك'], 403);
        }

        $review->update([
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'تم تعديل المراجعة بنجاح',
            'review'  => $review
        ]);
    }

    // حذف مراجعة
    public function destroy($id)
    {
        $review = review::findOrFail($id);

        if ($review->booking->renter_id !== Auth::id()) {
            return response()->json(['message' => 'لا يمكنك حذف مراجعة لا تخصك'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'تم حذف المراجعة بنجاح']);
    }
}
