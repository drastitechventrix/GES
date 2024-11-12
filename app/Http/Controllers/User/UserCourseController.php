<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class UserCourseController extends Controller
{
    //
    public function list(){
  $courses = Course::all(['id', 'title', 'image', 'price']); // Specify the fields you want to retrieve

    // Transform the response if needed (optional)
    $courses->transform(function ($course) {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'image' => asset('storage/' . $course->image), // Generate full URL for the image
            'price' => $course->price,
        ];
    });

    return response()->json(['courses' => $courses], 200);
    }

    public function show(Request $request){
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

    // Prepare the course details for response
    $courseDetails = [
        'id' => $course->id,
        'title' => $course->title,
        'description' => $course->description,
        'image' => asset('storage/' . $course->image), // Generate full URL for the image
        'price' => $course->price,
        // Add any other fields you want to include
    ];

    return response()->json(['course' => $courseDetails], 200);
    }
}
