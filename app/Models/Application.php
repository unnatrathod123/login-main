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
        'user_id', // ⭐ REQUIRED added on 23/02/2026
    ];

     protected $casts = 
     [
        'email_verified_at' => 'datetime',
    ];


    public const STATUS_APPLIED = 'applied';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW = 'interview_scheduled';

    public static function statuses(): array
    {
        return [
            
        self::STATUS_APPLIED => 'Applied',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_SHORTLISTED => 'Shortlisted',
        self::STATUS_INTERVIEW => 'Interview Scheduled',

    
        ];
    }


    public function createUser()
    {
        // return User::create([
        //     'name' => $this->name,
        //     'email' => $this->email,
        //     'password' => Hash::make('password123'),
        //     'email_verified_at' => $this->email_verified_at,
        //     'role' => 'intern',
        // ]);
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
