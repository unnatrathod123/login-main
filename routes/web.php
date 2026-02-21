<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request; // For Email Link
use App\Models\Application; // For Email Link


Route::get('/', function () {
    return redirect('admin');
});


// For Email Verification through Link
Route::get('/email/verify/{id}/{hash}', function (Request $request) {

    if (! $request->hasValidSignature()) {
        abort(403, 'Invalid or expired link');
    }

    $applicant = Application::findOrFail($request->id);

    if (! hash_equals((string)$request->hash, sha1($applicant->email))) {
        abort(403);
    }

     // mark verified
    $applicant->email_verified_at = now();
    $applicant->save();

    // redirect back to Next.js
    return redirect
        (
                'http://localhost:3000/email-verified'
        );

})->name('verification.verify');


