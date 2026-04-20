<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\Lecture;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class LectureDeliveryManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_manage_lecture_delivery_configuration(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->signInAdmin(['content.view', 'content.manage']);

        $lecture = Lecture::query()->firstWhere('slug', 'newton-laws-core');

        $response = $this->put(route('admin.lectures.update', $lecture), [
            'grade_id' => $lecture->grade_id,
            'track_id' => $lecture->track_id,
            'curriculum_section_id' => $lecture->curriculum_section_id,
            'lecture_section_id' => $lecture->lecture_section_id,
            'title' => $lecture->title,
            'slug' => $lecture->slug,
            'short_description' => $lecture->short_description,
            'long_description' => $lecture->long_description,
            'thumbnail_url' => $lecture->thumbnail_url,
            'type' => $lecture->type->value,
            'price_amount' => $lecture->price_amount,
            'currency' => $lecture->currency,
            'duration_minutes' => $lecture->duration_minutes,
            'published_at' => optional($lecture->published_at)->toDateTimeString(),
            'sort_order' => $lecture->sort_order,
            'is_active' => 1,
            'is_featured' => $lecture->is_featured ? 1 : 0,
            'is_free' => $lecture->is_free ? 1 : 0,
            'completion_threshold_percent' => 95,
            'assets' => [
                [
                    'kind' => 'external_video',
                    'title' => 'بث محدث للمحاضرة',
                    'url' => 'https://example.com/updated-newton-video',
                    'body' => 'رابط العرض الجديد.',
                    'sort_order' => 1,
                    'is_active' => 1,
                ],
            ],
            'checkpoints' => [
                [
                    'title' => 'منتصف الشرح',
                    'position_seconds' => 1200,
                    'sort_order' => 1,
                    'is_required' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.lectures.index'));

        $lecture->refresh();

        $this->assertDatabaseHas('lecture_assets', [
            'lecture_id' => $lecture->id,
            'title' => 'بث محدث للمحاضرة',
        ]);
        $this->assertDatabaseHas('lecture_checkpoints', [
            'lecture_id' => $lecture->id,
            'title' => 'منتصف الشرح',
        ]);
        $this->assertSame(95.0, (float) data_get($lecture->metadata, 'completion_threshold_percent'));
    }

    public function test_admin_without_manage_permission_cannot_mutate_lecture_delivery_configuration(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->signInAdmin(['content.view']);

        $lecture = Lecture::query()->firstWhere('slug', 'newton-laws-core');

        $this->put(route('admin.lectures.update', $lecture), [
            'grade_id' => $lecture->grade_id,
            'track_id' => $lecture->track_id,
            'curriculum_section_id' => $lecture->curriculum_section_id,
            'lecture_section_id' => $lecture->lecture_section_id,
            'title' => $lecture->title,
            'slug' => $lecture->slug,
            'short_description' => $lecture->short_description,
            'long_description' => $lecture->long_description,
            'thumbnail_url' => $lecture->thumbnail_url,
            'type' => $lecture->type->value,
            'price_amount' => $lecture->price_amount,
            'currency' => $lecture->currency,
            'duration_minutes' => $lecture->duration_minutes,
            'published_at' => optional($lecture->published_at)->toDateTimeString(),
            'sort_order' => $lecture->sort_order,
            'is_active' => 1,
        ])->assertForbidden();
    }

    public function test_admin_can_inspect_lecture_progress_page_when_allowed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->signInAdmin(['content.view']);

        $lecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');

        $this->get(route('admin.lectures.progress.index', $lecture))
            ->assertOk()
            ->assertSeeText('طالب مشترك');
    }

    public function test_admin_without_view_permission_cannot_inspect_lecture_progress_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->signInAdmin(['dashboard.view']);

        $lecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');

        $this->get(route('admin.lectures.progress.index', $lecture))
            ->assertForbidden();
    }
}
