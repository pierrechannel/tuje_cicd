<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;



class AuthController extends Controller
{
    /**
     * User Registration
     */
    public function register(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,user,manager'
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user' // Default role
            ]);

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User Login
     */
    public function login(Request $request): JsonResponse
{
    try {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json(['status' => 'error', 'message' => 'The username or password provided is incorrect.'], 401);
        }

        $user = Auth::user();

        // Optionally, you can delete existing tokens to enforce single token usage
        // $user->tokens()->delete();

        // Generate a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Login attempt failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Login failed. Please try again later.'
        ], 500);
    }
}

    /**
     * User Logout
     */
    /**
 * User Logout
 */
public function logout(Request $request): JsonResponse
{
    try {
        // Check if the user is authenticated
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found or already logged out'
            ], 401);
        }

        // Delete the current API token
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // Optional: If using session authentication, you can log the user out of web sessions
        Auth::guard('web')->logout();

        // Invalidate and regenerate session if it exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Logout failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Logout failed. Please try again later.'
        ], 500);
    }
}

    /**
     * Get Authenticated User Details
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Optionally load related resources
            $user->load(['sales', 'expenses', 'licenses']);

            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserPermissions($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json($user->getPermissions());
        }

        return response()->json(['error' => 'User not found'], 404);
    }


}
