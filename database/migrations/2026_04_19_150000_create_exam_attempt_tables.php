<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->longText('prompt');
            $table->longText('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('question_choices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('max_score')->default(1);
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
        });

        Schema::create('exam_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('answered_questions')->default(0);
            $table->unsignedInteger('correct_answers_count')->nullable();
            $table->unsignedInteger('total_score')->nullable();
            $table->unsignedInteger('max_score')->nullable();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedInteger('time_limit_snapshot')->nullable();
            $table->json('result_meta')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('exam_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->string('selected_answer')->nullable();
            $table->json('answer_payload')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->unsignedInteger('awarded_score')->nullable();
            $table->unsignedInteger('max_score')->default(0);
            $table->json('answer_meta')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('question_choices');
        Schema::dropIfExists('questions');
    }
};
