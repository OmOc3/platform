<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 40);
            $table->string('title');
            $table->string('url')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lecture_id', 'is_active', 'sort_order']);
        });

        Schema::create('lecture_checkpoints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position_seconds')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lecture_id', 'sort_order']);
        });

        Schema::create('lecture_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->unsignedInteger('last_position_seconds')->default(0);
            $table->unsignedInteger('consumed_seconds')->default(0);
            $table->decimal('completion_percent', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('last_checkpoint_id')->nullable()->constrained('lecture_checkpoints')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'lecture_id']);
            $table->index(['lecture_id', 'completion_percent']);
            $table->index(['student_id', 'last_opened_at']);
            $table->index(['lecture_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecture_progress');
        Schema::dropIfExists('lecture_checkpoints');
        Schema::dropIfExists('lecture_assets');
    }
};
