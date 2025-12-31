<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'message' => 'Invalid phoone or password'
            ], 401);
        }
        $user = Auth::user();
        if ($user->role !== 'admin' || $user->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => 'You are not authorized to access this route'
            ], 403);
        }
        $token = $user->createToken('admin-token')->plainTextToken;
        return response()->json([
            'message' => 'Admin login successful',
            'token'   => $token,
            'user'    => $user
        ], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
