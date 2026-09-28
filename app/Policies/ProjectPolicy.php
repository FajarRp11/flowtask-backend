<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->workspace
            ->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        $isProjectManager = $project->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', ProjectRole::Manager->value)
            ->exists();

        if ($isProjectManager) {
            return true;
        }

        return $project->workspace
            ->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [
                WorkspaceRole::Owner->value,
                WorkspaceRole::Admin->value,
            ])
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    public function createTask(User $user, Project $project): bool
    {
        $isProjectManager = $project->members()
            ->where('users.id', $user->id)
            ->wherePivot(
                'role',
                ProjectRole::Manager->value
            )
            ->exists();

        if ($isProjectManager) {
            return true;
        }

        return $project->workspace
            ->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [
                WorkspaceRole::Owner->value,
                WorkspaceRole::Admin->value,
            ])
            ->exists();
    }
}
