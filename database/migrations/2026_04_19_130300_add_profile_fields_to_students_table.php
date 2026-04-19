<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->foreignId('owner_admin_id')->nullable()->after('governorate')->constrained('admins')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->after('owner_admin_id')->constrained('grades')->nullOnDelete();
            $table->foreignId('track_id')->nullable()->after('grade_id')->constrained('tracks')->nullOnDelete();
            $table->foreignId('center_id')->nullable()->after('track_id')->constrained('educational_centers')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->after('center_id')->constrained('educational_groups')->nullOnDelete();
            $table->string('source_type')->default('online')->after('status');
            $table->boolean('is_azhar')->default(false)->after('source_type');
            $table->text('notes')->nullable()->after('is_azhar');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_admin_id');
            $table->dropConstrainedForeignId('grade_id');
            $table->dropConstrainedForeignId('track_id');
            $table->dropConstrainedForeignId('center_id');
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn(['source_type', 'is_azhar', 'notes']);
        });
    }
};
