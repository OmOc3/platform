<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\Grade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class AcademicManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_create_grade_and_track(): void
    {
        $this->signInAdmin(['grades.view', 'grades.manage', 'tracks.view', 'tracks.manage']);

        $this->post(route('admin.grades.store'), [
            'name_ar' => 'الصف الثاني الثانوي',
            'name_en' => 'Second Secondary',
            'code' => 'grade-2',
            'sort_order' => 2,
            'is_active' => true,
        ])->assertRedirect(route('admin.grades.index'));

        $grade = Grade::query()->firstWhere('code', 'grade-2');

        $this->post(route('admin.tracks.store'), [
            'grade_id' => $grade->id,
            'name_ar' => 'علمي علوم',
            'name_en' => 'Science',
            'code' => 'science',
            'sort_order' => 1,
            'is_active' => true,
        ])->assertRedirect(route('admin.tracks.index'));

        $this->assertDatabaseHas('tracks', [
            'grade_id' => $grade->id,
            'code' => 'science',
        ]);
    }
}
