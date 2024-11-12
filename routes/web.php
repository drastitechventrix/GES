<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/about', function () {
    return view('AboutUs'); // This will return the about.blade.php view
});

Route::get('/privacy-policy', function () {
    return view('PrivacyPolicy'); // This will return the privacy-policy.blade.php view
});

Route::get('/terms-conditions', function () {
    return view('TermsConditions'); // This will return the terms-conditions.blade.php view
});