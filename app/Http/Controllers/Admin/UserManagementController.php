<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    public function getpending()
    {
        $users = User::where('status', 'pending')->get();

        return response()->json([
            'message' => 'Pending registration requests',
            'users'   => $users
        ]);
    }

    public function approveuser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'message' => 'User not found'
                ],
                404
            );
        }
        if ($user->status !== 'pending') {
            return response()->json(['message' => 'User is not pending'], 400);
        }
        $user->update(['status' => 'active']);
        return response()->json(['message' => 'User approved successfully', 'user' => $user]);
    }

    public function rejectuser(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        // التحقق من وجود المستخدم
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user = User::findOrFail($id);

        if ($user->status !== 'pending') {
            return response()->json([
                'message' => 'User is not pending'
            ], 400);
        }

        $user->update([
            'status'            => 'rejected',
            'rejection_reason'  => $request->rejection_reason
        ]);

        return response()->json([
            'message' => 'User rejected successfully',
            'user'    => $user
        ]);
    }

    public function deleteuser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin user'], 403);
        }
        if ($user->personal_photo) {
            Storage::disk('public')->delete($user->personal_photo);
        }
        if ($user->id_photo) {
            Storage::disk('public')->delete($user->id_photo);
        }

        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ], 200);
    }

    //ارجاع كل اليوزرس
    public function getAllUsers()
    {
        $users = User::select(
            'id',
            'first_name',
            'last_name',
            'phone',
            'status',
            'role',
            'date_of_birth',
            'personal_photo',
            'id_photo'
        )->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'users' => $users]);
    }
}
