<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_teams', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_support_team', function (Blueprint $table): void {
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('support_team_id')->constrained('support_teams')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['admin_id', 'support_team_id']);
        });

        Schema::create('support_ticket_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('default_team_id')->nullable()->constrained('support_teams')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('support_ticket_type_id')->constrained('support_ticket_types')->restrictOnDelete();
            $table->foreignId('support_team_id')->nullable()->constrained('support_teams')->nullOnDelete();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('subject');
            $table->string('status')->default('open');
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['support_team_id', 'status']);
            $table->index(['assigned_admin_id', 'status']);
        });

        Schema::create('support_ticket_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->nullableMorphs('author');
            $table->longText('body');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_ticket_types');
        Schema::dropIfExists('admin_support_team');
        Schema::dropIfExists('support_teams');
    }
};
