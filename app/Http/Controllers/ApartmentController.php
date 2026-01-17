<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\apartment;
use App\Http\Requests\ApartmentRequest;
use App\Http\Requests\updateapartmentreq;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    public function index()
    {
        $apartments = Apartment::with(['owner:id,first_name,last_name,phone'])->get();
        return response()->json($apartments, 200);
    }
    public function store(ApartmentRequest $request)
    {
        $user = Auth::user();
        if ($user->role !== 'owner')
            return response()->json([
                'message' => 'Only owners can create apartments'
            ], 403);
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is not active. Please contact admin.'
            ], 403);
        }
        $validata = $request->validated();
        $validata['owner_id'] = $user->id;
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photodata = $photo->store('apartmentsphoto', 'public');
                $photos[] = $photodata;
                //او لارجاع روابط بدل مسارات
                //$photos[] = Storage::url($photodata);
            }
        }
        $validata['photos'] = json_encode($photos);;
        $apartment = apartment::create($validata);
        return response()->json([
            'message' => 'Apartment created successfully',
            'apartment' => $apartment
        ], 201);
    }

    public function update(updateapartmentreq $request, apartment $apartment)
    {
        if (!$apartment->is_available) {
            return response()->json([
                'message' => 'this apartment is renter'
            ], 403);
        }
        if (Auth::id() !== $apartment->owner_id) {
            return response()->json([
                'message' => 'You are not authorized to update this apartment'
            ], 403);
        }
        $data = $request->validated();

        if ($request->hasFile('photos')) {
            $oldPhotos = json_decode($apartment->photos, true) ?? [];
            foreach ($oldPhotos as $oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $newPhotos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('apartmentsphoto', 'public');
                $newPhotos[] = $path;
            }
            $data['photos'] = json_encode($newPhotos);
        } else {
            unset($data['photos']);
        }
        $apartment->update($data);

        return response()->json([
            'message' => 'Apartment updated successfully',
            'data' => $apartment
        ]);
    }
    public function show($id)
{
    $apartment = Apartment::find($id);

    if (!$apartment) {
        return response()->json([
            'message' => 'Apartment not found'
        ], 404);
    }

    $apartment->load([
        'owner:id,first_name,phone,qr_photo',
        'booking:id,apartment_id,start_date,end_date',
        'booking.review:id,booking_id,rating,comment'
    ]);

    $apartment->photos = json_decode($apartment->photos, true) ?? [];

    foreach ($apartment->booking ?? [] as $booking) {
        unset($booking->apartment_id);
    }

    $averageRating = $apartment->booking()
        ->with('review')
        ->get()
        ->pluck('review.rating')
        ->filter()
        ->avg();

    return response()->json([
        'apartment'      => $apartment,
        'average_rating' => $averageRating ?? 0,
    ], 200);
}


    public function destroy(apartment $apartment)
    {
        if (Auth::id() !== $apartment->owner_id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $apartment->delete();

        return response()->json(['message' => 'Apartment deleted successfully']);
    }
    public function filter(Request $request)
    {
        $query = apartment::query();

        if ($request->province) {
            $query->where('province', $request->province);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->rooms) {
            $query->where('count_room', $request->rooms);
        }

        return response()->json($query->get());
    }
    public function myapartments()
    {
        $apartments = apartment::where('owner_id', Auth::id())->get();
        return response()->json($apartments);
    }
}
