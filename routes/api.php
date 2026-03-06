<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController; // For Verifying Email And Saving Input
use App\Http\Controllers\Api\AuthController; // For Authentication


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// For Email Verification Through Link
Route::post('/applicant/send-verification', [ApplicationController::class,'sendVerification']);

Route::get('/email/verify/{id}/{hash}',
    [ApplicationController::class, 'verifyEmail']
)->name('verification.verify');

// For Submitting Application
Route::post('/applicant/submit', [ApplicationController::class,'submitApplication']);


// For Intern Login
Route::post('/intern/login', [AuthController::class, 'internLogin']);
// For Admin Login
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// For Authenticate
Route::middleware('auth:sanctum')->group(function () 
{
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);
});


// // For Email Test
// Route::get('/mail-test', function () {
//     Mail::raw('SMTP Test OK', function ($m) {
//         $m->to('unnatrathod1024@gmail.com')
//           ->subject('SMTP Working');
//     });
// });

    // API for Intern
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::post('/task/submit/{id}', [TaskController::class, 'submitTask']);
    });

// For Creating User from Applications
    Route::post('/applications/{id}/create-user', 
    [ApplicationController::class, 'createUser']);