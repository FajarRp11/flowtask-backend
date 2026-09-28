<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\Label;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\Response;

class LabelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Label $label): bool
    {
        return $label->workspace
            ->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [
                WorkspaceRole::Owner->value,
                WorkspaceRole::Admin->value,
            ])
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Label $label): bool
    {
        return $this->canManageWorkspace(
            $user,
            $label->workspace
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Label $label): bool
    {
        return $this->canManageWorkspace(
            $user,
            $label->workspace
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Label $label): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Label $label): bool
    {
        return false;
    }

    private function canManageWorkspace(
        User $user,
        Workspace $workspace
    ): bool {
        return $workspace->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [
                WorkspaceRole::Owner->value,
                WorkspaceRole::Admin->value,
            ])
            ->exists();
    }
}
