<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()
            ->with('assignees')
            ->latest()
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('createTask', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                'in:todo,in_progress,review,done',
            ],

            'priority' => [
                'nullable',
                'in:low,medium,high,urgent',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],
        ]);

        $task = $project->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'todo',
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $task->load([
            'project',
            'assignees',
        ]);

        return (new TaskResource($task))
            ->additional([
                'message' => 'Task created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        $task->load([
            'project',
            'assignees',
        ]);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'sometimes',
                'required',
                'in:todo,in_progress,review,done',
            ],

            'priority' => [
                'sometimes',
                'required',
                'in:low,medium,high,urgent',
            ],

            'due_date' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ]);

        $task->update($validated);

        return (new TaskResource($task->fresh()))
            ->additional([
                'message' => 'Task updated successfully.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
