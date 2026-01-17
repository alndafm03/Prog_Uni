<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\favorite;
use App\Models\apartment;

class FavoriteController extends Controller
{
    public function  toggleFavorite($apartment_id)
    {
        $user = Auth::user();

        if ($user->role !== 'renter') {
            return response()->json([
                'message' => 'Only renters can add favorites'
            ], 403);
        }
        $apartment = apartment::find($apartment_id);
        if (!$apartment) {
            return response()->json([
                'message' => 'Apartment not found'
            ], 404);
        }
        $exists = favorite::where('user_id', $user->id)
            ->where('apartment_id', $apartment_id)
            ->first();

        if ($exists) {
            $exists->delete();
            return response()->json(['message' => 'Removed from favorites']);
        }

        favorite::create([
            'user_id' => $user->id,
            'apartment_id' => $apartment_id
        ]);

        return response()->json(['message' => 'Added to favorites']);
    }
    public function getfavorites()
    {
        $user = Auth::user();
        $favorites = favorite::where('user_id', $user->id)->with([
            'apartment.owner:id,first_name,phone',
        ])->get()
            ->map(function ($fav) {
                $fav->apartment->photos = json_decode($fav->apartment->photos, true) ?? [];
                return $fav->apartment;
            });
        return response()->json(['favorites' => $favorites]);
    }
    public function removefavorite($apartment_id)
{
    $user = Auth::user();

    $favorite = favorite::where('user_id', $user->id)
        ->where('apartment_id', $apartment_id)
        ->first();

    if (!$favorite) {
        return response()->json([
            'message' => 'This apartment is not in your favorites'
        ], 404);
    }

    $favorite->delete();

    return response()->json([
        'message' => 'Apartment removed from favorites successfully'
    ]);
}

}
