<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Workspace $workspace)
    {
        Gate::authorize('viewAny', [Label::class, $workspace]);

        $labels = $workspace->labels()
            ->latest()
            ->get();

        return LabelResource::collection($labels);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Workspace $workspace)
    {
        Gate::authorize('create', [Label::class, $workspace]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
            ],

            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ]);

        $label = $workspace->labels()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6B7280',
        ]);

        $label->load('workspace');

        return (new LabelResource($label))
            ->additional([
                'message' => 'Label created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Label $label)
    {
        Gate::authorize('view', $label);

        $label->load('workspace');

        return new LabelResource($label);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Label $label)
    {
        Gate::authorize('update', $label);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
            ],

            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ]);

        $label->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6B7280',
        ]);

        $label->load('workspace');

        return new LabelResource($label);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Label $label)
    {
        Gate::authorize('delete', $label);

        $label->delete();

        return response()->json([
            'message' => 'Label deleted successfully.',
        ]);
    }
}
