<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mistake_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('lecture_id')->nullable()->constrained('lectures')->nullOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->string('question_reference')->nullable();
            $table->longText('question_text');
            $table->longText('correct_answer_snapshot')->nullable();
            $table->longText('model_answer_snapshot')->nullable();
            $table->longText('explanation')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('score_lost')->nullable();
            $table->json('score_meta')->nullable();
            $table->string('source');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'lecture_id']);
            $table->index(['student_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mistake_items');
    }
};
