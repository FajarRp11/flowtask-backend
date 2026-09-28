<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'workspace_id',
    'name',
    'color',
])]
class Label extends Model
{
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(
            Task::class,
            'task_labels'
        )
            ->withTimestamps();
    }
}
