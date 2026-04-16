<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|integer|exists:roles,id',
        ]);
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role_id = $request->input('role_id');
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $role_id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'User registered successfully', 'user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user -> load('role');

        return response()->json(['message' => 'User logged in successfully', 'user' => $user, 'token' => $token], 200);
    }

    public function user(Request $request)
    {
        // Get authenticated user and eager load role
        $user = $request->user()->load('role');

        return response()->json([
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Delete current access token (Sanctum)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
