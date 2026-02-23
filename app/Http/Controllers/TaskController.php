<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;

class TaskController extends Controller
{
    //
    public function myTasks(Request $request)
    {
        return Task::where('intern_id', $request->user()->id)->get();
    }

    // Submitting Task
     public function submitTask(Request $request, $id)
    {
        $task = Task::where('id', $id)
                    ->where('intern_id', $request->user()->id)
                    ->firstOrFail();

        $request->validate([
            'submission' => 'required|mimes:pdf,docx,zip,jpg,png|max:10240'
        ]);

        $filePath = $request->file('submission')
                            ->store('task_submissions', 'public');

        $task->update([
            'submission_file' => $filePath,
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        return response()->json(['message' => 'Task submitted successfully']);
    }
}
