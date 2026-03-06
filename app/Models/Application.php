<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Notifications\VerifyEmailCustom;
use App\Models\Intern;

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
        'source',
        'skills',
        'resume_path',
        'status',
        'intern_id',
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
        return Intern::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make('password123'),
            'email_verified_at' => $this->email_verified_at,
           
        ]);
    }


    public function intern()
    {
        return $this->hasOne(Intern::class, 'application_id', 'application_id');
    }

    // For Custom application_id

    protected static function boot()
    {
       parent::boot();

        static::creating(function ($application) {

            $year = date('Y');

            $last = self::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->first();

            if ($last && $last->application_id) {
                // Split "IAPES/2026/001" into an array
                $parts = explode('/', $last->application_id);
                
                // Grab the very last item in the array ("001") instead of index [1]
                $lastNumber = (int) end($parts);
                
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $application->application_id = 'APP/' . $year . '/' . $newNumber;
        });
    }
    //--------------------------------

    

}
