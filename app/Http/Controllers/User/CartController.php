<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;

class CartController extends Controller
{
      public function add(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
           'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]);
        }

        $userId = $request->user()->id; // Get the authenticated user's ID

        // Check if the course is already in the cart
        $existingCartItem = Cart::where('user_id', $userId)->where('course_id', $request->course_id)->first();

        if ($existingCartItem) {
            return response()->json(['message' => 'Course already in cart.'], 400);
        }

        // Create a new cart item
        Cart::create([
            'user_id' => $userId,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['message' => 'Course added to cart successfully.'], 200);
    }

    // Remove a course from the cart
    public function remove(Request $request)
    {

         $validator = Validator::make($request->all(), [
           'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]);
        }
     

    $userId = $request->user()->id; // Get the authenticated user's ID

    // Find the cart item for the authenticated user and the specified course
    $cartItem = Cart::where('user_id', $userId)->where('course_id', $request->course_id)->first();

    if (!$cartItem) {
        return response()->json(['message' => 'Course not found in cart.'], 404);
    }

    // Delete the cart item
    $cartItem->delete();

    return response()->json(['message' => 'Course removed from cart successfully.'], 200);

    }

    // Get all courses in the cart
    public function index(Request $request)
    {
        $userId = $request->user()->id; // Get the authenticated user's ID

        $cartItems = Cart::where('user_id', $userId)->with('course')->get();

        return response()->json(['cartlist' => $cartItems], 200);
    }
}
