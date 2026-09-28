<?php

namespace App\Http\Controllers\Api;

use App\Enums\WorkspaceRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\WorkspaceMemberResource;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkspaceMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Workspace $workspace)
    {
        Gate::authorize('view', $workspace);

        $workspace->load('members');

        return WorkspaceMemberResource::collection(
            $workspace->members
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Workspace $workspace, Request $request)
    {
        Gate::authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'uuid',
                'exists:users,public_id',
            ],
            'role' => [
                'required',
                'in:admin,member,viewer',
            ],
        ]);

        $user = User::where(
            'public_id',
            $validated['user_id']
        )->firstOrFail();

        if ($workspace->members()
            ->where('users.id', $user->id)
            ->exists()
        ) {
            return response()->json([
                'message' => 'User is already a member of this workspace.',
            ], 422);
        }

        $workspace->members()->attach($user->id, [
            'role' => $validated['role'],
        ]);

        $member = $workspace->members()
            ->where('users.id', $user->id)
            ->first();

        return (new WorkspaceMemberResource($member))
            ->additional([
                'message' => 'Member added successfully.',
            ])
            ->response()
            ->setStatusCode(201);
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
    public function update(Workspace $workspace, User $user, Request $request)
    {
        Gate::authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'role' => [
                'required',
                'in:admin,member,viewer',
            ],
        ]);

        $membership = $workspace->members()
            ->where('users.id', $user->id)
            ->first();

        if (! $membership) {
            return response()->json([
                'message' => 'User is not a member of this workspace.',
            ], 404);
        }

        if ($membership->pivot->role === WorkspaceRole::Owner->value) {
            return response()->json([
                'message' => 'Workspace owner role cannot be changed.',
            ], 422);
        }

        $workspace->members()->updateExistingPivot(
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
    public function destroy(Workspace $workspace, User $user)
    {
        Gate::authorize('manageMembers', $workspace);

        $membership = $workspace->members()
            ->where('users.id', $user->id)
            ->first();

        if (! $membership) {
            return response()->json([
                'message' => 'User is not a member of this workspace.',
            ], 404);
        }

        // Owner tidak boleh dihapus
        if ($membership->pivot->role === WorkspaceRole::Owner->value) {
            return response()->json([
                'message' => 'Workspace owner cannot be removed.',
            ], 422);
        }

        $workspace->members()->detach($user->id);

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }
}
