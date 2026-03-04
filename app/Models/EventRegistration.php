<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    //
    protected $fillable = [
        'event_id',
       // 'user_id',
        'name',
        'email',
        'phone',
        'institution',
        'attendance_status',
        'certificate_issued',
        'certificate_number',
        'certificate_path'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
