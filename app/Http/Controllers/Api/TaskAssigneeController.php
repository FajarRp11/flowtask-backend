<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskAssigneeResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskAssigneeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        Gate::authorize('view', $task);

        $task->load('assignees');

        return TaskAssigneeResource::collection($task->assignees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        Gate::authorize('manageAssignees', $task);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'uuid',
                'exists:users,public_id',
            ],
        ]);

        $user = User::where(
            'public_id',
            $validated['user_id']
        )->firstOrFail();

        $isProjectMember = $task->project
            ->members()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isProjectMember) {
            return response()->json([
                'message' => 'User must be a member of the project first.',
            ], 422);
        }

        $isAlreadyAssigned = $task->assignees()
            ->where('users.id', $user->id)
            ->exists();

        if ($isAlreadyAssigned) {
            return response()->json([
                'message' => 'User is already assigned to this task.',
            ], 422);
        }

        $task->assignees()->attach($user->id);

        $task->load('assignees');

        return response()->json([
            'message' => 'User assigned to task successfully.',
            'assignee' => new TaskAssigneeResource($user),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task, User $user)
    {
        Gate::authorize('manageAssignees', $task);

        $isAssigned = $task->assignees()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isAssigned) {
            return response()->json([
                'message' => 'User is not assigned to this task.',
            ], 422);
        }

        $task->assignees()->detach($user->id);

        return response()->json([
            'message' => 'User unassigned from task successfully.',
        ]);
    }
}
