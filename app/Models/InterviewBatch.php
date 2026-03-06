<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Application;
use App\Models\InterviewAssignment;

class InterviewBatch extends Model
{
    //
    protected $fillable = [
        'interview_batch_code',
        'interview_batch_name',
        'interview_date',
        'start_time',
        'end_time',
        'interview_location',
        'batch_size',
        'capacity_status',
        'workflow_status'
    ];
    protected static function booted()
    {
        static::creating(function ($batch) {

            $month = now()->format('m');
            $year  = now()->format('y');

            // Get latest batch for current month/year
            $latestBatch = self::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->latest('id')
                ->first();

            if ($latestBatch) {
                $lastNumber = (int) substr($latestBatch->interview_batch_code, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $batch->interview_batch_code = "IB-{$month}{$year}-{$newNumber}";
        });

        
    }

    public function assignments()
    {
        return $this->hasMany(InterviewAssignment::class);
    }

    public function updateCapacityStatus()
    {
        $assignedCount = $this->assignments()->count();

        if ($assignedCount >= $this->batch_size) {
            $this->update(['capacity_status' => 'full']);
        } else {
            $this->update(['capacity_status' => 'open']);
        }
    }
}
