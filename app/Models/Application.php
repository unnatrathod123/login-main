<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Notifications\VerifyEmailCustom;

class Application extends Model implements MustVerifyEmail
{
    //
       // use HasFactory;
      use Notifiable, MustVerifyEmailTrait;

        protected $fillable = 
    [
        'email',
        'name',
        'phone',
        'college',
        'degree',
        // ADD THESE:
        'last_exam_appeared',
        'cgpa',
        'domain',
        'duration',
        'duration_unit',
        'skills',
        'resume_path',
        'status',
       
    ];

     protected $casts = 
     [
        'email_verified_at' => 'datetime',
    ];


    public static function statuses(): array
    {
        return [
            
            'applied' => 'Applied',
            'rejected'  => 'Rejected',
            'shortlisted' => 'shortlisted',
            'interview_scheduled' => 'Interview Scheduled',
    
        ];
    }
    /**
     * Check if applicant has verified email
     */
    // public function isEmailVerified(): bool
    // {
    //     return !is_null($this->email_verified_at);
    // }

    
    // public function sendEmailVerificationNotification()
    // {
    //     $this->notify(new VerifyEmailCustom());
    // }
}
