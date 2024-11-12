<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }
        
        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            $token = Str::random(100);
            $admin->token = $token;
            $admin->save();
            Auth::login($admin);
            return response()->json(['message' => 'Login successful', 'data'=>$admin], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $admin = Admin::where('id', '1')->first();

        if ($admin) {
            $admin->token = null; // Set the token to null
            $user->save(); // Save the user
        } 
    
        Auth::logout();
        return response()->json(['message' => 'Logout successful'], 200);
    }
}