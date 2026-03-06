<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Intern extends Authenticatable
{
    //
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'intern_id';

    public $incrementing = false;   // ❌ not auto increment
    protected $keyType = 'string';  // ✅ string id

     protected $fillable = 
    [
        
        'intern_id',
        'application_id',
        'password',
        'start_date',
        'end_date',
        'status',
    ];



    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }


    /**
     * Create intern from application
     */
    public static function createFromApplication($application)
    {
        $year = now()->format('y');

        $lastIntern = self::where('intern_id', 'like', "INT/$year/%")
            ->orderBy('intern_id', 'desc')
            ->first();

        $number = $lastIntern
            ? ((int) substr($lastIntern->intern_id, -3)) + 1
            : 1;

        $internId = "INT/$year/" . str_pad($number, 3, '0', STR_PAD_LEFT);

        $password = Str::random(8);

        $intern = self::create([
            'intern_id' => $internId,
            'application_id' => $application->application_id,
            'password' => Hash::make($password),
            'start_date' => now(),
            'end_date' => null,
            'status' => 'active'
        ]);

        $application->update([
            'intern_id' => $internId
        ]);

        return [
            'intern' => $intern,
            'password' => $password
        ];
    }
}
