<?php

namespace App\Http\Controllers;

use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show all users' profiles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all(); // Retrieve all users
        return response()->json($users); // Return all users as JSON
    }

    /**
     * Show a specific user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user); // Return user as JSON
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user(); // Retrieve the authenticated user

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated); // Update user details

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Update the authenticated user's password.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user(); // Get the currently authenticated user

        // Check if the current password is correct
        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        // Attempt to update the password
        try {
            $user->password = Hash::make($request->newPassword); // Hash the new password
            $user->save(); // Save changes

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            // Log any exception for debugging
            \Log::error('Password update failed: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Failed to update password. Please try again later.'], 500);
        }
    }
    /**
     * Upload a profile image for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $user = Auth::user(); // Get the authenticated user

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image && Storage::exists($user->image)) {
                Storage::delete($user->image);
            }

            $imagePath = $request->file('image')->store('images/profile', 'public');
            $user->image = $imagePath; // Set image path
            $user->save(); // Save changes

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image' => asset('storage/' . $user->image),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image uploaded',
        ], 400);
    }
}
