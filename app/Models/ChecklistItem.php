<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'public_id',
    'task_id',
    'title',
    'is_completed',
    'sort_order',
])]
class ChecklistItem extends Model
{
    use HasPublicId;

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
