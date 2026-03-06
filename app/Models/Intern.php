<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intern extends Model
{
    //
    protected $primaryKey = 'intern_id';

    public $incrementing = true;

    protected $keyType = 'int';

     protected $fillable = 
    [
        
        // 'intern_id',
        'application_id',
        'start_date',
        'end_date',
        'status',
    ];



    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }
}
