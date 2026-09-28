<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load('members');

        return ProjectMemberResource::collection(
            $project->members
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('manageMembers', $project);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'uuid',
                'exists:users,public_id',
            ],
            'role' => [
                'required',
                'in:manager,member',
            ],
        ]);

        $user = User::where(
            'public_id',
            $validated['user_id']
        )->firstOrFail();

        $isWorkspaceMember = $project->workspace
            ->members()
            ->where('users.id', $user->id)
            ->exists();

        if (! $isWorkspaceMember) {
            return response()->json([
                'message' => 'User must be a member of the workspace first.',
            ], 422);
        }

        // Cek apakah sudah menjadi project member
        if ($project->members()
            ->where('users.id', $user->id)
            ->exists()
        ) {
            return response()->json([
                'message' => 'User is already a member of this project.',
            ], 422);
        }

        $project->members()->attach($user->id, [
            'role' => $validated['role'],
        ]);

        $member = $project->members()
            ->where('users.id', $user->id)
            ->first();

        return (new ProjectMemberResource($member))
            ->additional([
                'message' => 'Project member assigned successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Request $request)
    {
        Gate::authorize('manageMembers', $project);

        $validated = $request->validate([
            'role' => [
                'required',
                'in:manager,member',
            ],
        ]);

        $membership = $project->members()
            ->where('users.id', $user->id)
            ->first();

        if (! $membership) {
            return response()->json([
                'message' => 'User is not a member of this project.',
            ], 404);
        }

        $project->members()->updateExistingPivot(
            $user->id,
            [
                'role' => $validated['role'],
            ]
        );

        return response()->json([
            'message' => 'Member role updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user)
    {
        Gate::authorize('manageMembers', $project);

        $membership = $project->members()
            ->where('users.id', $user->id)
            ->first();

        if (! $membership) {
            return response()->json([
                'message' => 'User is not a member of this project.',
            ], 404);
        }

        $project->members()->detach($user->id);

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }
}
