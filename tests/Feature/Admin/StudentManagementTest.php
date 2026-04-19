<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use App\Modules\Identity\Models\Admin;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\StudentStatus;
use Database\Seeders\AcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_filter_students_and_update_their_status(): void
    {
        $this->seed(AcademicSeeder::class);

        $grade = Grade::query()->first();
        $track = Track::query()->where('grade_id', $grade->id)->first();
        $owner = Admin::factory()->create();
        $center = EducationalCenter::factory()->create();
        $group = EducationalGroup::factory()->create(['center_id' => $center->id]);

        $student = Student::factory()->create([
            'name' => 'طالب تجريبي',
            'status' => StudentStatus::Pending,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
        ]);

        $this->signInAdmin(['students.view', 'students.manage']);

        $this->get(route('admin.students.index', ['status' => StudentStatus::Pending->value]))
            ->assertOk()
            ->assertSee('طالب تجريبي');

        $this->put(route('admin.students.update', $student), [
            'name' => 'طالب مفعّل',
            'email' => $student->email,
            'phone' => $student->phone,
            'parent_phone' => $student->parent_phone,
            'governorate' => 'القاهرة',
            'status' => StudentStatus::Subscribed->value,
            'status_reason' => 'تمت المراجعة',
            'source_type' => 'hybrid',
            'is_azhar' => false,
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'center_id' => $center->id,
            'group_id' => $group->id,
            'owner_admin_id' => $owner->id,
            'notes' => 'مفعل عبر الإدارة',
        ])->assertRedirect(route('admin.students.edit', $student));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'طالب مفعّل',
            'status' => StudentStatus::Subscribed->value,
            'source_type' => 'hybrid',
            'owner_admin_id' => $owner->id,
        ]);

        $this->assertDatabaseHas('student_status_histories', [
            'student_id' => $student->id,
            'previous_status' => StudentStatus::Pending->value,
            'new_status' => StudentStatus::Subscribed->value,
        ]);
    }
}
