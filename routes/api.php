<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController; // For Verifying Email And Saving Input



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/intern', [ApplicationController::class, 'application']);


// For Email Verification Through Link
Route::post('/applicant/send-verification', [ApplicationController::class,'sendVerification']);

// For Submitting Application
Route::post('/applicant/submit', [ApplicationController::class,'submitApplication']);


Route::get('/email/verify/{id}/{hash}',
    [ApplicationController::class, 'verifyEmail']
)->name('verification.verify');


// For Email Test
// Route::get('/mail-test', function () {
//     Mail::raw('SMTP Test OK', function ($m) {
//         $m->to('unnatrathod1024@gmail.com')
//           ->subject('SMTP Working');
//     });
// });