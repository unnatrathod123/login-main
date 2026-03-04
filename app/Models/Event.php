<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'title',
        'type',
        'description',
        'start_date',
        'end_date',
        'location',
        'certificate_template',
        'status',
      //  'created_by'
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
    
}
