<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('open');
            $table->string('visibility')->default('public');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('forum_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained('forum_threads')->cascadeOnDelete();
            $table->nullableMorphs('author');
            $table->longText('body');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_message_id')->constrained('forum_messages')->cascadeOnDelete();
            $table->string('type');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_attachments');
        Schema::dropIfExists('forum_messages');
        Schema::dropIfExists('forum_threads');
    }
};
