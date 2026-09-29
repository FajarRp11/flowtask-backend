<?php

namespace App\Policies;

use App\Models\ChecklistItem;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class ChecklistItemPolicy
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
    public function view(User $user, ChecklistItem $checklistItem): bool
    {
        return $checklistItem->task
            ->project
            ->workspace
            ->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Task $task): bool
    {
        return Gate::forUser($user)->allows('manage', $task->project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChecklistItem $checklistItem): bool
    {
        return Gate::forUser($user)->allows('manage', $checklistItem->task->project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChecklistItem $checklistItem): bool
    {
        return Gate::forUser($user)->allows('manage', $checklistItem->task->project);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChecklistItem $checklistItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChecklistItem $checklistItem): bool
    {
        return false;
    }

    public function reorder(User $user, Task $task): bool
    {
        return Gate::forUser($user)->allows(
            'manage',
            $task->project
        );
    }
}
