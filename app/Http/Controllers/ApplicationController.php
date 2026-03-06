<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Application;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    
    // SEND VERIFICATION LINK
   //-----------------------------------------------------------
  
    // For Submitting form
public function submitApplication(Request $request)
{
    $resumePath = null;

    if ($request->hasFile('resume_path')) 
    {
        $file = $request->file('resume_path');
        
        // 1. Get the original extension (e.g., 'pdf', 'docx')
        $extension = $file->getClientOriginalExtension();
        
        // 2. Make the name safe for a URL/folder (e.g., "Unnat Rathod" -> "unnat-rathod")
        $safeName = Str::slug($request->name);
        
        // 3. Construct the new file name with a timestamp to prevent overwriting
        // Result: unnat-rathod-1710500000.pdf
        $fileName = $safeName .'.' . $extension;
        
        // 4. Use storeAs() to save it with our custom name
        $resumePath = $file->storeAs('resumes', $fileName, 'public');
    }

    Application::updateOrCreate(
        ['email' => $request->email], // condition to find record
        [
            'name' => $request->name,
            'phone' => $request->phone,
            'college' => $request->college,
            'degree' => $request->degree,
            'last_exam_appeared' => $request->last_exam_appeared,
            'cgpa' => $request->cgpa,
            'domain' => $request->domain,
            'duration' => $request->duration,
            'duration_unit' => $request->duration_unit,
            'skills' => $request->skills,
            'resume_path' => $resumePath,
            'source' => $request->source,
        ]
    );

    return response()->json([
        'message' => 'Application submitted successfully'
    ]);
}
    
// For creating user on 23/02/2026
    public function shortlisted()
    {
        return Application::where('status', 'shortlisted')
            ->whereNull('intern_id')
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
    //     return DB::transaction(function () {
    //     $year = date('Y');

    //     // Order by 'id' to ensure we get the absolute latest, even if created_at timestamps match
    //     $last = Application::whereYear('created_at', $year)
    //             ->lockForUpdate()
    //             ->latest('id') 
    //             ->first();

    //     if ($last && $last->application_id) {
    //         // Split "IAPES/2026/001" by the slash and grab the last part ("001")
    //         $parts = explode('/', $last->application_id);
    //         $lastNumber = intval(end($parts)); 
    //         $number = $lastNumber + 1;
    //     } else {
    //         $number = 1;
    //     }

    //     return "IAPES/$year/" . str_pad($number, 3, '0', STR_PAD_LEFT);
    // });
    }
   
}

