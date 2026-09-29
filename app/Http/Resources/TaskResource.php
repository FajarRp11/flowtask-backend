<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,

            'title' => $this->title,
            'description' => $this->description,

            'status' => $this->status,
            'priority' => $this->priority,

            'due_date' => $this->due_date?->toDateString(),

            'project' => new ProjectResource(
                $this->whenLoaded('project')
            ),

            'assignees' => UserResource::collection(
                $this->whenLoaded('assignees')
            ),

            'checklist_items' => ChecklistItemResource::collection(
                $this->whenLoaded('checklistItems')
            ),
        ];
    }
}
