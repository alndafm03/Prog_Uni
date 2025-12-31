<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateRequest;
use App\Models\User;


class UserController extends Controller
{
    public function register(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        if ($request->hasFile('personal_photo')) {
            $data['personal_photo'] = $request->file('personal_photo')->store('Myphoto', 'public');
        }
        if ($request->hasFile('id_photo')) {
            $data['id_photo'] = $request->file('id_photo')->store('Myphoto', 'public');
        }
        $user = User::create($data);
        return response()->json([
            'message' => 'Register Successfuly',
            'User' => $user
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only(['phone', 'password'])))
            return response()->json(['message' => 'Invalid phone or password']);
        $user = User::where('phone', $request->phone)->firstOrFail();

        if ($user->status === 'pending') {
            return response()->json(
                [
                    'message' => 'Your account is pending approval. Please wait for admin confirmation'
                ],
                403
            );
        } else if ($user->status === 'rejected') {
            return response(['message' => 'Your account has been rejected. Please contact support'], 403);
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Login successfuly',
                'User' => $user,
                'token' => $token
            ], 200);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successfully'
        ]);
    }
    public function getprofile()
    {
        $user = Auth::User();
        return response()->json([
            'message' => 'User profile retrieved successfully',
            $user
        ], 200);
    }
    public function updateprofile(UpdateRequest $request) //باستخدام ال x-www-form....
    {
        $user = $request->user();
        $data = $request->validated();
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        if ($request->hasFile('id_photo')) {
            if ($user->id_photo) {
                Storage::disk('public')->delete($user->id_photo);
            }
            $data['id_photo'] = $request->file('id_photo')->store('Myphoto', 'public');
        }
        if ($request->hasFile('personal_photo')) {
            if ($user->personal_photo) {
                Storage::disk('public')->delete($user->personal_photo);
            }
            $data['personal_photo'] = $request->file('personal_photo')->store('Myphoto', 'public');
        }
        $user->update($data);

        if ($user->wasChanged()) {
            return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'No changes detected', 'user' => $user], 200);
        }
    }
    //حذف الحساب نهائيا
    public function deleteAccount(Request $request){
        $user=$request->user();
        if ($user->personal_photo){
            Storage::disk('public')->delete($user->personal_photo);
        }
        if ($user->id_photo){
            Storage::disk('public')->delete($user->id_photo);
        }
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
        'message' => 'Account deleted successfully'
    ], 200);
    }
}
