<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    // Display a listing of the users
    public function index()
    {
        $users = User::all(); // Retrieve all users
        return response()->json($users); // Return users as JSON
    }

    // Store a newly created user in storage
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,user,manager', // Role validation
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user with the provided password attribute
        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            // Password is automatically hashed by the model's mutator
            'password' => '12345678', // Default password
        ]);

        return response()->json($user, 201); // For successful creation
 // 201 Created response
    }

    // Display the specified user
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user); // Return user as JSON
    }

    // Update the specified user in storage
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($id) // Ignore the current user for uniqueness
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id) // Ignore the current user for uniqueness
            ],
            'role' => 'required|string|in:admin,user,manager',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['username', 'name', 'email', 'role']));

        return response()->json($user);
    }

    // Remove the specified user from storage
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete(); // Delete the user

        return response()->json(['message' => 'User deleted successfully.']); // Return success message
    }

    // Update the authenticated user's password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = $request->newPassword; // Automatically hashed by the model
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
