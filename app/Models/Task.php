<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
     protected $fillable = [
        'title',
        'description',
        'intern_id',
        'deadline',
        'status',
        'submission_file',
        'submitted_at',
        'feedback'
    ];  

    public function intern()
    {
        return $this->belongsTo(User::class, 'intern_id');
    }
}
