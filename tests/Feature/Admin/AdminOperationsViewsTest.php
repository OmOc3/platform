<?php

namespace Tests\Feature\Admin;

use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Support\Models\Complaint;
use App\Shared\Enums\ComplaintStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class AdminOperationsViewsTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_review_centers_attendance_and_complaints(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin([
            'centers.view',
            'attendance.view',
            'complaints.view',
            'complaints.manage',
        ]);

        $center = EducationalCenter::query()->firstOrFail();
        $complaint = Complaint::query()->firstOrFail();

        $this->get(route('admin.centers.index'))
            ->assertOk()
            ->assertSeeText($center->name_ar)
            ->assertSeeText('أقسام الإدارة')
            ->assertSeeText('ابحث في السناتر');

        $this->get(route('admin.centers.show', $center))
            ->assertOk()
            ->assertSeeText($center->name_ar);

        $this->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSeeText('محاضرة قوانين نيوتن');

        $this->get(route('admin.complaints.index'))
            ->assertOk()
            ->assertSeeText($complaint->student?->name ?? '');

        $this->put(route('admin.complaints.update', $complaint), [
            'status' => ComplaintStatus::Resolved->value,
            'admin_notes' => 'تم التواصل مع الطالب وتوضيح الإجراء.',
        ])->assertRedirect(route('admin.complaints.show', $complaint));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => ComplaintStatus::Resolved->value,
            'admin_notes' => 'تم التواصل مع الطالب وتوضيح الإجراء.',
        ]);
    }

    public function test_center_attendance_and_complaint_routes_are_permission_protected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin(['dashboard.view']);

        $this->get(route('admin.centers.index'))->assertForbidden();
        $this->get(route('admin.attendance.index'))->assertForbidden();
        $this->get(route('admin.complaints.index'))->assertForbidden();
    }
}
