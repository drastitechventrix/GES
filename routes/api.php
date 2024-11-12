<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\LegalContentController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\UserCourseController;
use App\Http\Controllers\User\CartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);


Route::middleware('check.admintoken')->group(function () {
    Route::post('/courses-create', [CourseController::class, 'create']);
    Route::post('/courses-update', [CourseController::class, 'update']);
    Route::post('/courses-delete', [CourseController::class, 'delete']); // Soft delete
    Route::get('/contact-list', [AdminContactController::class, 'list']);

    // Route::post('/courses/restore/{id}', [CourseController::class, 'restore']); // Restore soft delete
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/contact-us', [ContactController::class, 'store']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']); // Add this line
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/courses-list', [UserCourseController::class, 'list']);
    Route::post('/courses-detail', [UserCourseController::class, 'show']);
     Route::post('/cart-add', [CartController::class, 'add']); // Add course to cart
    Route::post('/cart-remove', [CartController::class, 'remove']); // Remove course from cart
    Route::get('/cart', [CartController::class, 'index']); // Get all courses in the cart

});
