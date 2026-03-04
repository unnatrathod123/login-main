<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Application;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        if ($applicant->hasVerifiedEmail()) 
        {
                    
                /*
            |--------------------------------------------------------------------------
            | ✅ CHECK IF USER ALREADY APPLIED
            |--------------------------------------------------------------------------
            | If name OR phone already exists, assume form already submitted
            */

             if (!empty($applicant->name) || !empty($applicant->phone)) 
                {
                    return response()->json([
                        'message' => 'You have already applied.',
                        'status'  => 'already_applied'
                    ], 409); // 409 = Conflict
                }
            //----------------------------------------------------------------------------------
            {
                    return response()->json([
                            'message'  => 'Email already verified earlier',
                            'verified' => true
                    ], 200);
                }
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
            'refer' => 'required|in:website,social,linkedin,college,friend',

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
                'refer' => $validated['refer'],
            ]);

            return response()->json([
                'message' => 'Application submitted successfully'
            ]);
    }

    
// For creating user on 23/02/2026
    public function shortlisted()
    {
        return Application::where('status', 'shortlisted')
            ->whereNull('user_id')
            ->get();
    }



//---------- For Application Submission in Batch ------------------------

   public function storeBatch(Request $request)
    {
        $request->validate([
            'applications' => 'required|array|min:2|max:5'
        ]);

        $success = [];
        $failed = [];

        foreach ($request->applications as $index => $applicationData) {

            try {

                // Validate each application
                $validator = validator($applicationData, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:applications,email',
                    'contact_number' => 'required|regex:/^[0-9]{10,15}$/',
                    'domain' => 'required|string',
                ]);

                if ($validator->fails()) {
                    $failed[] = [
                        'index' => $index,
                        'email' => $applicationData['email'] ?? null,
                        'errors' => $validator->errors()
                    ];
                    continue; // Skip this one and move next
                }

                // Individual transaction per application
                DB::transaction(function () use ($applicationData) {

                    Application::create([
                        'application_id' => $this->generateApplicationId(),
                        'name' => $applicationData['name'],
                        'email' => $applicationData['email'],
                        'phone' => $applicationData['contact_number'],
                        'domain' => $applicationData['domain'],
                    ]);
                });

                $success[] = [
                    'index' => $index,
                    'email' => $applicationData['email']
                ];

            } catch (\Exception $e) {

                $failed[] = [
                    'index' => $index,
                    'email' => $applicationData['email'] ?? null,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return response()->json([
            'saved' => $success,
            'failed' => $failed
        ]);
    }

    private function generateApplicationId()
    {
        return DB::transaction(function () {

            $year = date('Y');

            $last = Application::whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->latest()
                    ->first();

            $number = $last 
                ? intval(substr($last->application_id, -3)) + 1 
                : 1;

            return "IAPES/$year/" . str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }
   
}

