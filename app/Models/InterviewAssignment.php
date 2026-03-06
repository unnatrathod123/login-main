<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Application;
use App\Models\InterviewBatch;

class InterviewAssignment extends Model
{
    //
    protected $fillable = [
        'assignment_code',
        'interview_batch_id',
        'application_id',
        'attendance',
        'problem_solving',
        'communication',
        'overall_score',
        'remarks',
        'result'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            $date = now()->format('my');

            $sequence = str_pad(
                InterviewAssignment::whereDate('created_at', now())->count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

            $model->assignment_code = "ASSIGN/{$date}/{$sequence}";

            $batch = InterviewBatch::find($model->interview_batch_id);

            if ($batch->capacity_status === 'full') {
                throw new \Exception("Batch is already FULL.");
            }
        });

        static::saving(function ($assignment) {

            if ($assignment->problem_solving && $assignment->communication) {
                $assignment->overall_score =
                    ($assignment->problem_solving + $assignment->communication);
            }
                 
            if ($assignment->overall_score > 50) {
                throw new \Exception("Total marks cannot exceed 50");
            }
            
            if ($assignment->attendance === 'present') {
                $assignment->application->update([
                    'status' => 'interviewed'
                ]);
            }

            if ($assignment->result === 'selected') {
                $assignment->application->update([
                    'status' => 'shortlisted'
                ]);
            }

            if ($assignment->result === 'rejected') {
                $assignment->application->update([
                    'status' => 'rejected'
                ]);
            }
        });

        static::updated(function ($assignment) {

            if ($assignment->result) {

                if ($assignment->result === 'selected') {
                    $assignment->application
                        ->update(['status' => 'shortlisted']);
                } else {
                    $assignment->application
                        ->update(['status' => 'rejected']);
                }
            }
        });

        static::created(function ($assignment) {

            // Update Application Status
            $assignment->application
                ->update(['status' => 'interview_scheduled']);

            // Update Batch Capacity
            $assignment->batch->updateCapacityStatus();
        });

        static::deleted(function ($assignment) {

            if ($assignment->batch) {
                $assignment->batch->updateCapacityStatus();
            }

        });
    }

    
    public function batch()
    {
        return $this->belongsTo(
            InterviewBatch::class,
            'interview_batch_id',
            'id'
        );
    }

    public function application()
    {
        return $this->belongsTo(
            Application::class,
            'application_id',
            'id'
        );
    }
}
