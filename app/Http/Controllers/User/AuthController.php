<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\OTP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            // 'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:15', 
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }

      $user = User::withTrashed()->where('email', $request->email)->first();
         if ($user) {
             // If the user exists and is NOT soft-deleted
             if (!$user->trashed()) {
          
            return response()->json(['message' => 'Email already exists. Please use a different email.'], 409);
        }
      
        $user->forceDelete(); // Permanently delete the soft-deleted user

        // If the user is soft-deleted, we can create a new account with the same email
       }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name, 
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number, // Add phone number

        ]);

        return response()->json(['message' => 'User  registered successfully','data'=>$user], 201);
    }

    public function login(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
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



        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // $token = Str::random(100);
            // $tokens = $user->token ? json_decode($user->token, true) : [];
            // $tokens[] = $token;
                    $token = $user->createToken('GES')->plainTextToken;

            // dd($token);
            $user->token = $token;
            // $user->token = $token;
            $user->save();
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'token' => $token,
            ], 201);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        // $user_id = $request->user_id; // Get the authenticated user
        // $user = User::where('id', $user_id)->first();

        // if ($user) {
        //     $user->token = null; // Set the token to null
        //     $user->save(); // Save the user
        $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Successfully logged out.'], 200);
        // }
    
        return response()->json(['error' => 'User not authenticated.'], 401);
    
    }

    public function sendOtp(Request $request)
  {
        $validator = Validator::make($request->all(), [
                   'email' => 'required|email|exists:users,email',
        ]);
         // dd($request->all());
          // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }

    $user = User::where('email', $request->email)->first();
    $otp = $this->generateOtp(); // Generate the OTP

    // Send the OTP notification
    $user->notify(new SendOtpNotification($otp));

    // Optionally, you can store the OTP in the database or cache for verification later
   Otp::create([
        'email' => $request->email,
        'otp' => $otp,
        'expires_at' => now()->addMinutes(5), // Set expiration time
    ]);

    return response()->json(['message' => 'OTP sent successfully!','otp'=>$otp], 200);
  }

  public function verifyOtp(Request $request)
  {
  
  $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        'otp' => 'required|digits:4',
        ]);
          // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }


    // Retrieve the OTP from the cache or database
      $otpRecord = Otp::where('email', $request->email)
        ->where('otp', $request->otp)
        ->where('expires_at', '>', now()) // Check if the OTP is still valid
        ->first();


  if ($otpRecord) {
        // OTP is valid
        $otpRecord->delete(); // Optionally delete the OTP record after successful verification

        return response()->json(['message' => 'OTP verified successfully!'], 200);
    }

    return response()->json(['message' => 'Invalid OTP or OTP has expired.'], 401);

  }

  public function generateOtp()
  {
        return rand(1000, 9999); // Generates a random 4-digit number
  }

    public function resetPassword(Request $request)   
    {

    // Validate the new password
 
      $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed', // Ensure password confirmation
        ]);
          // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }


    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ]);
    }

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    // Update the user's password
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['message' => 'Password updated successfully!'], 200);
    }

   public function deleteAccount(Request $request)
    {
        // Ensure the user is authenticated
        $user = $request->user();
        // dd($user);

        if ($user) {
            // Soft delete the user
            $user->delete();

            return response()->json(['message' => 'User  account deleted successfully.'], 200);
        }

        return response()->json(['error' => 'User  not authenticated.'], 401);
    }

     public function updateProfile(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Validate the request
        $validator = Validator::make($request->all(), [
           'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }

        // Update the user's profile
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone_number = $request->phone_number;
        $user->name = $request->first_name . ' ' . $request->last_name;

        // Save the changes
        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'data' => $user], 200);
    }

    public function changePassword(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }

        // Get the authenticated user
        $user = $request->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

}
    // Techventrix@1234

    