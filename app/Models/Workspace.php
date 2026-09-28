<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['public_id', 'owner_id', 'name', 'slug', 'description'])]
class Workspace extends Model
{
    use HasPublicId;

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships()
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'workspace_members',
            'workspace_id',
            'user_id'
        )
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}
