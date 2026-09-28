<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();

            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');

            $table->string('name');

            $table->string('slug');

            $table->text('description')->nullable();

            $table->string('status', 20)
                ->default('planning');

            $table->string('priority', 20)
                ->default('medium');

            $table->date('start_date')->nullable();

            $table->date('due_date')->nullable();

            $table->timestamps();

            $table->unique([
                'workspace_id',
                'slug',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
