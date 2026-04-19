<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Commerce\Models\Book;
use App\Modules\Commerce\Models\Package;
use App\Modules\Support\Models\ForumThread;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class PhaseThreeManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_manage_new_catalog_modules_and_support_views(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin([
            'content.view',
            'content.manage',
            'packages.view',
            'packages.manage',
            'books.view',
            'books.manage',
            'forum.view',
            'forum.reply',
            'mistakes.view',
        ]);

        $grade = Grade::query()->firstWhere('code', 'grade-1-secondary');
        $track = Track::query()->firstWhere('code', 'foundation-track');

        $this->get(route('admin.curriculum-sections.index'))
            ->assertOk();

        $this->post(route('admin.curriculum-sections.store'), [
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'name_ar' => 'وحدة الزخم',
            'name_en' => 'Momentum',
            'slug' => 'momentum-unit',
            'description' => 'وحدة جديدة لاختبار إدارة أقسام المنهج.',
            'sort_order' => 9,
            'is_active' => true,
        ])->assertRedirect(route('admin.curriculum-sections.index'));

        $curriculumSection = CurriculumSection::query()->firstWhere('slug', 'momentum-unit');

        $this->post(route('admin.lecture-sections.store'), [
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $curriculumSection->id,
            'name_ar' => 'محاضرات الزخم',
            'name_en' => 'Momentum Lessons',
            'slug' => 'momentum-lessons',
            'description' => 'قسم فرعي جديد للمحاضرات.',
            'sort_order' => 9,
            'is_active' => true,
        ])->assertRedirect(route('admin.lecture-sections.index'));

        $lectureSection = LectureSection::query()->firstWhere('slug', 'momentum-lessons');

        $this->post(route('admin.lectures.store'), [
            'grade_id' => $grade->id,
            'track_id' => $track->id,
            'curriculum_section_id' => $curriculumSection->id,
            'lecture_section_id' => $lectureSection->id,
            'title' => 'محاضرة الزخم الأساسية',
            'slug' => 'momentum-masterclass',
            'short_description' => 'شرح تأسيسي لمفهوم الزخم.',
            'long_description' => 'شرح أطول لاختبار إنشاء المحاضرات وربطها بمنتج رقمي.',
            'type' => 'lecture',
            'price_amount' => 175,
            'currency' => 'EGP',
            'duration_minutes' => 55,
            'sort_order' => 9,
            'is_active' => true,
            'is_featured' => true,
            'is_free' => false,
        ])->assertRedirect(route('admin.lectures.index'));

        $lecture = Lecture::query()->firstWhere('slug', 'momentum-masterclass');

        $this->post(route('admin.packages.store'), [
            'name_ar' => 'باقة الزخم',
            'name_en' => 'Momentum Package',
            'slug' => 'momentum-package',
            'teaser' => 'باقة قصيرة لاختبار إدارة الباقات.',
            'description' => 'تجميع محاضرات الزخم في باقة واحدة.',
            'price_amount' => 320,
            'currency' => 'EGP',
            'billing_cycle_label' => 'باقة خاصة',
            'access_period_days' => 45,
            'item_ids' => [$lecture->id],
            'overlap_rule' => 'block',
            'is_active' => true,
            'is_featured' => false,
        ])->assertRedirect(route('admin.packages.index'));

        $this->post(route('admin.books.store'), [
            'name_ar' => 'كتاب الزخم',
            'name_en' => 'Momentum Book',
            'slug' => 'momentum-book',
            'teaser' => 'كتاب مرافق للوحدة.',
            'description' => 'كتاب مطبوع لاختبار إدارة الكتب.',
            'price_amount' => 210,
            'currency' => 'EGP',
            'author_name' => 'فريق الإتقان',
            'page_count' => 180,
            'stock_quantity' => 25,
            'cover_badge' => 'إصدار جديد',
            'availability_status' => 'in_stock',
            'is_active' => true,
            'is_featured' => true,
        ])->assertRedirect(route('admin.books.index'));

        $this->assertNotNull(Package::query()->whereHas('product', fn ($query) => $query->where('slug', 'momentum-package'))->first());
        $this->assertNotNull(Book::query()->whereHas('product', fn ($query) => $query->where('slug', 'momentum-book'))->first());

        $thread = ForumThread::query()->firstOrFail();

        $this->get(route('admin.forum-threads.index'))
            ->assertOk()
            ->assertSeeText($thread->title);

        $this->post(route('admin.forum-threads.reply', $thread), [
            'body' => 'تمت متابعة الموضوع من الإدارة.',
        ])->assertRedirect(route('admin.forum-threads.show', $thread));

        $this->get(route('admin.mistakes.index'))
            ->assertOk()
            ->assertSeeText('قوانين نيوتن الأساسية');
    }

    public function test_forum_management_routes_are_permission_protected(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->signInAdmin(['dashboard.view']);

        $this->get(route('admin.forum-threads.index'))
            ->assertForbidden();
    }
}
