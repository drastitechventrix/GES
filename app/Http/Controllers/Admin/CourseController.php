<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function create(Request $request)
    {
          $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }
       
        // Handle the image upload
        $imagePath = $request->file('image')->store('images/courses', 'public');

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'admin_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
    }

    public function update(Request $request, $id)
    {
     
       $validator = Validator::make($request->all(), [
          'course_id' => 'required|exists:courses,id', // Ensure course_id is provided and exists
        'title' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'price' => 'sometimes|required|numeric',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
        ]);
            // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }
    

          $course = Course::find($request->course_id);

    // Check if the course exists
    if (!$course) {
        return response()->json(['message' => 'Course not found.'], 404);
    }

        // Handle the image upload if a new image is provided
        if ($request->hasFile('image')) {
            // Delete the old image if necessary (optional)
            // Storage::disk('public')->delete($course->image);
            $imagePath = $request->file('image')->store('images/courses', 'public');
            $course->image = $imagePath;
        }

        $course->update($request->only(['title', 'description', 'price']));

        return response()->json(['message' => 'Course updated successfully', 'course' => $course]);
    }


    public function delete(Request $request)
    {

        // dd($request->all());
        // Validate the request
    $validator = Validator::make($request->all(), [
        'course_id' => 'required|exists:courses,id', // Ensure course_id is provided and exists
    ]);

    // Check if validation fails
     if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]); // Unprocessable Entity
        }
    

    // Find the course by course_id
    $course = Course::find($request->course_id);

    // Check if the course exists
    if (!$course) {
        return response()->json(['message' => 'Course not found.'], 404);
    }

    // Optionally delete the image associated with the course
    // Storage::disk('public')->delete($course->image); // Uncomment if you want to delete the old image

    // Delete the course
    $course->delete();

    return response()->json(['message' => 'Course deleted successfully.'], 200);

    }
    
}
