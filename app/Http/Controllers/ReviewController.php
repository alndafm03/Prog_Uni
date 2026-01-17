<?php

namespace App\Http\Controllers;

use App\Models\review;
use App\Models\booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = review::with('booking')->get();
        return response()->json($reviews);
    }

    public function showapartmentreview($apartment_id)
{
    $reviews = review::whereHas('booking', function ($query) use ($apartment_id) {
            $query->where('apartment_id', $apartment_id);
        })
        ->select('id', 'rating', 'comment', 'created_at')
        ->get();

    return response()->json($reviews);
}


    public function store(Request $request, $booking_id)
    {
        $request->validate([
            'rating'  => 'required|in:1,2,3,4,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = booking::findOrFail($booking_id);

        if ($booking->renter_id !== Auth::id()) {
            return response()->json(['message' => 'لا يمكنك إضافة مراجعة لحجز لا يخصك'], 403);
        }

        if (!in_array($booking->status, ['approved', 'completed'])) {
            return response()->json(['message' => 'لا يمكنك إضافة مراجعة إلا بعد الموافقة على الحجز أو اكتماله'], 403);
        }
        $review = review::create([
            'booking_id' => $booking->apartment_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return response()->json([
            'message' => 'تم إضافة المراجعة بنجاح',
            'review'  => $review
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|in:1,2,3,4,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = review::findOrFail($id);

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
