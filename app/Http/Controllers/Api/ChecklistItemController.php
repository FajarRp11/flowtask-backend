<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistItemResource;
use App\Models\ChecklistItem;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Task $task)
    {
        Gate::authorize('view', $task);

        $checklistItems = $task->checklistItems()
            ->orderBy('sort_order')
            ->get();

        return ChecklistItemResource::collection(
            $checklistItems
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        Gate::authorize('create', $task);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $nextSortOrder = ($task->checklistItems()->max('sort_order') ?? -1) + 1;

        $checklistItem = $task->checklistItems()->create([
            'title' => $validated['title'],
            'is_completed' => false,
            'sort_order' => $nextSortOrder,
        ]);

        return (new ChecklistItemResource($checklistItem))
            ->additional([
                'message' => 'Checklist item created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ChecklistItem $checklistItem)
    {
        Gate::authorize('view', $checklistItem);

        return new ChecklistItemResource($checklistItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChecklistItem $checklistItem)
    {
        Gate::authorize('update', $checklistItem);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'is_completed' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $checklistItem->update($validated);

        return new ChecklistItemResource($checklistItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChecklistItem $checklistItem)
    {
        Gate::authorize('delete', $checklistItem);

        $checklistItem->delete();

        return response()->json([
            'message' => 'Checklist item deleted successfully.',
        ]);
    }

    public function reorder(
        Request $request,
        Task $task
    ) {
        Gate::authorize('reorder', $task);

        $validated = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.id' => [
                'required',
                'uuid',
                'exists:checklist_items,public_id',
            ],

            'items.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        foreach ($validated['items'] as $item) {
            $checklistItem = $task->checklistItems()
                ->where('public_id', $item['id'])
                ->firstOrFail();

            $checklistItem->update([
                'sort_order' => $item['sort_order'],
            ]);
        }

        $checklistItems = $task->checklistItems()
            ->orderBy('sort_order')
            ->get();

        return ChecklistItemResource::collection(
            $checklistItems
        );
    }
}
