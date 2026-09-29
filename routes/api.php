<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChecklistItemController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\TaskAssigneeController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskLabelController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\WorkspaceMemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('workspaces', WorkspaceController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::get(
        'workspaces/{workspace}/members',
        [WorkspaceMemberController::class, 'index']
    );

    Route::post(
        'workspaces/{workspace}/members',
        [WorkspaceMemberController::class, 'store']
    );

    Route::patch(
        'workspaces/{workspace}/members/{user}',
        [WorkspaceMemberController::class, 'update']
    );

    Route::delete(
        'workspaces/{workspace}/members/{user}',
        [WorkspaceMemberController::class, 'destroy']
    );

    Route::get(
        'projects/{project}/members',
        [ProjectMemberController::class, 'index']
    );

    Route::post(
        'projects/{project}/members',
        [ProjectMemberController::class, 'store']
    );

    Route::patch(
        'projects/{project}/members/{user}',
        [ProjectMemberController::class, 'update']
    );

    Route::delete(
        'projects/{project}/members/{user}',
        [ProjectMemberController::class, 'destroy']
    );

    Route::get(
        'projects/{project}/tasks',
        [TaskController::class, 'index']
    );

    Route::post(
        'projects/{project}/tasks',
        [TaskController::class, 'store']
    );

    Route::get(
        'tasks/{task}',
        [TaskController::class, 'show']
    );

    Route::patch(
        'tasks/{task}',
        [TaskController::class, 'update']
    );

    Route::delete(
        'tasks/{task}',
        [TaskController::class, 'destroy']
    );

    Route::get(
        'tasks/{task}/assignees',
        [TaskAssigneeController::class, 'index']
    );

    Route::post(
        'tasks/{task}/assignees',
        [TaskAssigneeController::class, 'store']
    );

    Route::delete(
        'tasks/{task}/assignees/{user}',
        [TaskAssigneeController::class, 'destroy']
    );

    Route::get(
        'workspaces/{workspace}/labels',
        [LabelController::class, 'index']
    );

    Route::post(
        'workspaces/{workspace}/labels',
        [LabelController::class, 'store']
    );

    Route::get(
        'labels/{label}',
        [LabelController::class, 'show']
    );

    Route::patch(
        'labels/{label}',
        [LabelController::class, 'update']
    );

    Route::delete(
        'labels/{label}',
        [LabelController::class, 'destroy']
    );

    Route::get(
        'tasks/{task}/labels',
        [TaskLabelController::class, 'index']
    );

    Route::post(
        'tasks/{task}/labels',
        [TaskLabelController::class, 'store']
    );

    Route::delete(
        'tasks/{task}/labels/{label}',
        [TaskLabelController::class, 'destroy']
    );

    Route::patch(
        'tasks/{task}/checklist/reorder',
        [ChecklistItemController::class, 'reorder']
    );

    Route::get(
        'tasks/{task}/checklist',
        [ChecklistItemController::class, 'index']
    );

    Route::post(
        'tasks/{task}/checklist',
        [ChecklistItemController::class, 'store']
    );

    Route::get(
        'checklist-items/{checklistItem}',
        [ChecklistItemController::class, 'show']
    );

    Route::patch(
        'checklist-items/{checklistItem}',
        [ChecklistItemController::class, 'update']
    );

    Route::delete(
        'checklist-items/{checklistItem}',
        [ChecklistItemController::class, 'destroy']
    );
});
