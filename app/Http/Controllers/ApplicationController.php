<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Application;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;


class ApplicationController extends Controller
{
    // SEND VERIFICATION LINK
public function sendVerification(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    // Find existing applicant OR create new
    $applicant = Application::firstOrCreate([
        'email' => $request->email
    ]);

    /*
    |--------------------------------------------------------------------------
    | ✅ IF EMAIL ALREADY VERIFIED
    |--------------------------------------------------------------------------
    */
    if ($applicant->hasVerifiedEmail()) {
        return response()->json([
            'message'  => 'Email already verified earlier',
            'verified' => true
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ SEND VERIFICATION EMAIL
    |--------------------------------------------------------------------------
    */
    $applicant->sendEmailVerificationNotification();

    return response()->json([
        'message'  => 'Verification link sent',
        'verified' => false
    ], 200);
}

    // VERIFY EMAIL
    public function verifyEmail(Request $request, $id, $hash)
    {
        $applicant = Application::findOrFail($id);

        if (! hash_equals(sha1($applicant->email), $hash)) {
            abort(403);
        }

        if (! $request->hasValidSignature()) {
            abort(403);
        }

        if (! $applicant->hasVerifiedEmail()) {
            $applicant->markEmailAsVerified();
        }

       return redirect
       (
             //'http://localhost:3000?verified=1&email=' . urlencode($applicant->email)
              config('app.frontend_url') . '/?verified=1&email=' . urlencode($applicant->email)
        );
    }
 //-------------------------------------------------------------------------------------



    // For Checking Email Is verified or not

    public function checkStatus(Request $request)
    {
        $request->validate([
        'email' => 'required|email'
    ]);

    $applicant = Application::where('email', $request->email)->first();

    return response()->json([
        'verified' => $applicant && $applicant->email_verified_at !== null
    ]);
    }
    //-------------------------------------------------------------------------------------

    // For Submitting form

    public function submitApplication(Request $request)
    {
         // ✅ VALIDATION RULES
    $validated = $request->validate([
        'email'   => 'required|email|exists:applications,email',
        'name'    => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        'phone'   => 'required|regex:/^[0-9]{10,15}$/',
        'college' => 'nullable|string',
        'degree'  => 'required|string',
        // ADD THESE LINES:
        'last_exam_appeared' => 'required|string|max:255',
        'cgpa' => 'required|numeric|between:0,100', // Validates number between 0 and 100
        'domain'  => 'required|string',
                
            // NEW
        'duration' => 'required|integer|min:1',
        'duration_unit' => 'required|in:months,days,hours',
        
        'skills'  => 'required|string',
        'resume_path'  => 'nullable|file|mimes:pdf|max:10240',
        
    ]);

    $applicant = Application::where('email', $request->email)->firstOrFail();

    // ✅ check email verification again (important)
    if (!$applicant->email_verified_at) 
        {
            return response()->json([
                'message' => 'Email not verified'
            ], 403);
        }

        $resumePath = null;

if ($request->hasFile('resume_path')) {
    $resumePath = $request->file('resume_path')
        ->store('resumes', 'public'); // storage/app/public/resumes
}
            // ✅ save application data
        $applicant->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'college' => $validated['college'],
            'degree' => $validated['degree'],
            'last_exam_appeared' => $validated['last_exam_appeared'],
            'cgpa' => $validated['cgpa'],
            'domain' => $validated['domain'],
            'duration' => $validated['duration'],
            'duration_unit' => $validated['duration_unit'],
            'skills' => $validated['skills'],
            'resume_path' => $resumePath,
            
           
        ]);

        return response()->json([
            'message' => 'Application submitted successfully'
        ]);
    }
}
