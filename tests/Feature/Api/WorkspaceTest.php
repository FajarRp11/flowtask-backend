<?php

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access workspace endpoints', function () {
    $workspace = Workspace::factory()->create();

    $this->getJson('/api/workspaces')->assertUnauthorized();
    $this->postJson('/api/workspaces', ['name' => 'New Workspace'])->assertUnauthorized();
    $this->getJson("/api/workspaces/{$workspace->id}")->assertUnauthorized();
    $this->putJson("/api/workspaces/{$workspace->id}", ['name' => 'Updated Workspace'])->assertUnauthorized();
    $this->deleteJson("/api/workspaces/{$workspace->id}")->assertUnauthorized();

    $this->getJson("/api/workspaces/{$workspace->id}/members")->assertUnauthorized();
    $this->postJson("/api/workspaces/{$workspace->id}/members", ['email' => 'user@example.com', 'role' => WorkspaceRole::Member->value])->assertUnauthorized();
});

test('user can list workspaces they belong to', function () {
    $user = User::factory()->create();
    $userWorkspaces = Workspace::factory()->count(2)->create();
    
    foreach ($userWorkspaces as $workspace) {
        $workspace->members()->attach($user->id, ['role' => WorkspaceRole::Member->value]);
    }

    $otherWorkspace = Workspace::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/workspaces');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $userWorkspaces[0]->id])
        ->assertJsonFragment(['id' => $userWorkspaces[1]->id])
        ->assertJsonMissing(['id' => $otherWorkspace->id]);
});

test('user can create a workspace', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $payload = [
        'name' => 'Test Workspace',
        'description' => 'A description for test workspace',
    ];

    $response = $this->postJson('/api/workspaces', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Test Workspace')
        ->assertJsonPath('data.description', 'A description for test workspace')
        ->assertJsonPath('message', 'Workspace created successfully.');

    $this->assertDatabaseHas('workspaces', [
        'name' => 'Test Workspace',
        'owner_id' => $user->id,
    ]);

    $workspaceId = $response->json('data.id');
    $this->assertDatabaseHas('workspace_user', [
        'workspace_id' => $workspaceId,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner->value,
    ]);
});

test('creating workspace fails validation when name is missing or too long', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->postJson('/api/workspaces', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->postJson('/api/workspaces', ['name' => str_repeat('a', 256)])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('user can view workspace details if member', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    $workspace->members()->attach($user->id, ['role' => WorkspaceRole::Owner->value]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/workspaces/{$workspace->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $workspace->id)
        ->assertJsonPath('data.name', $workspace->name);
});

test('user cannot view workspace if not a member (403 forbidden)', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $otherUser->id]);
    $workspace->members()->attach($otherUser->id, ['role' => WorkspaceRole::Owner->value]);

    Sanctum::actingAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}")
        ->assertForbidden();
});

test('viewing non-existent workspace returns 404', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/workspaces/999999')
        ->assertNotFound();
});

test('workspace owner or admin can update workspace', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);

    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);
    $workspace->members()->attach($admin->id, ['role' => WorkspaceRole::Admin->value]);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/workspaces/{$workspace->id}", [
        'name' => 'Updated Workspace Name',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Workspace Name')
        ->assertJsonPath('message', 'Workspace updated successfully.');

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'Updated Workspace Name',
    ]);
});

test('regular member cannot update workspace (403 forbidden)', function () {
    $member = User::factory()->create();
    $workspace = Workspace::factory()->create();
    $workspace->members()->attach($member->id, ['role' => WorkspaceRole::Member->value]);

    Sanctum::actingAs($member);

    $this->putJson("/api/workspaces/{$workspace->id}", [
        'name' => 'Unauthorized Update',
    ])->assertForbidden();
});

test('updating workspace fails validation with invalid data', function () {
    $owner = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);
    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);

    Sanctum::actingAs($owner);

    $this->putJson("/api/workspaces/{$workspace->id}", [
        'name' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('only owner can delete workspace', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $owner->id]);

    $workspace->members()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);
    $workspace->members()->attach($admin->id, ['role' => WorkspaceRole::Admin->value]);

    // Admin should fail (403)
    Sanctum::actingAs($admin);
    $this->deleteJson("/api/workspaces/{$workspace->id}")
        ->assertForbidden();

    // Owner should succeed
    Sanctum::actingAs($owner);
    $this->deleteJson("/api/workspaces/{$workspace->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Workspace deleted successfully.');

    $this->assertDatabaseMissing('workspaces', [
        'id' => $workspace->id,
    ]);
});
