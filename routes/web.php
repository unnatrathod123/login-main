<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request; // For Email Link
use App\Models\Application; // For Email Link


Route::get('/', function () {
    return redirect('admin');
});



// Route::get('/update-application-ids', function () {

//     $applications = Application::orderBy('id')->get();

//     foreach ($applications as $index => $app) {
//         $app->application_id = 'IAPES'.'/'.date('Y', strtotime($app->created_at)) . '/' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
//         $app->save();
//     }

//     return "Application IDs Updated Successfully!";
// });



// For Email Verification through Link ( We do not need this because we are using Laravel’s built-in system:)
// Route::get('/email/verify/{id}/{hash}', function (Request $request) {

//     if (! $request->hasValidSignature()) {
//         abort(403, 'Invalid or expired link');
//     }

//     $applicant = Application::findOrFail($request->id);

//     if (! hash_equals((string)$request->hash, sha1($applicant->email))) {
//         abort(403);
//     }

//      // mark verified
//     $applicant->email_verified_at = now();
//     $applicant->save();

//     // redirect back to Next.js
//     return redirect
//         (
//                // 'http://localhost:3000/email-verified'
//                config('app.frontend_url') . '/email-verified'
//         );

// })->name('verification.verify');


