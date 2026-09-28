<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskLabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        Gate::authorize('view', $task);

        $task->load('labels');

        return LabelResource::collection(
            $task->labels
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        Gate::authorize('manageLabels', $task);

        $validated = $request->validate([
            'label_id' => [
                'required',
                'uuid',
                'exists:labels,public_id',
            ],
        ]);

        $label = Label::where(
            'public_id',
            $validated['label_id']
        )->firstOrFail();

        if (
            $label->workspace_id !==
            $task->project->workspace_id
        ) {
            return response()->json([
                'message' => 'Label must belong to the same workspace as the task.',
            ], 422);
        }

        $alreadyAttached = $task->labels()
            ->where('labels.id', $label->id)
            ->exists();

        if ($alreadyAttached) {
            return response()->json([
                'message' => 'Label is already attached to this task.',
            ], 422);
        }

        $task->labels()->attach($label->id);

        return (new LabelResource($label))
            ->additional([
                'message' => 'Label attached to task successfully.',
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task, Label $label)
    {
        Gate::authorize('manageLabels', $task);

        /*
         * Pastikan label memang berada di
         * workspace yang sama.
         */
        if (
            $label->workspace_id !==
            $task->project->workspace_id
        ) {
            return response()->json([
                'message' => 'Label does not belong to the task workspace.',
            ], 422);
        }

        $attached = $task->labels()
            ->where('labels.id', $label->id)
            ->exists();

        if (! $attached) {
            return response()->json([
                'message' => 'Label is not attached to this task.',
            ], 404);
        }

        $task->labels()->detach($label->id);

        return response()->json([
            'message' => 'Label removed from task successfully.',
        ]);
    }
}
