<?php

namespace App\Policies;

use App\Enums\ProjectRole;
use App\Enums\WorkspaceRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
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
    public function view(User $user, Task $task): bool
    {
        return $task->project
            ->workspace
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
    public function update(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->canManageProject($user, $task);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }

    private function canManageProject(
        User $user,
        Task $task
    ): bool {
        $project = $task->project;

        $isProjectManager = $project
            ->members()
            ->where('users.id', $user->id)
            ->wherePivot(
                'role',
                ProjectRole::Manager->value
            )
            ->exists();

        if ($isProjectManager) {
            return true;
        }

        return $project
            ->workspace
            ->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [
                WorkspaceRole::Owner->value,
                WorkspaceRole::Admin->value,
            ])
            ->exists();
    }

    public function manageAssignees(User $user, Task $task): bool
    {
        $project = $task->project;

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

    public function manageLabels(User $user, Task $task): bool
    {
        $project = $task->project;

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
