<?php

namespace Tests\Feature;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLectureDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_with_access_can_view_lecture_delivery_content_and_related_exam_cta(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');
        $lecture = Lecture::query()->firstWhere('slug', 'newton-laws-core');

        $this->actingAs($student, 'student');

        $this->get(route('student.lectures.show', $lecture))
            ->assertOk()
            ->assertSeeText('الفيديو الرئيسي لقوانين نيوتن')
            ->assertSeeText('ابدأ الاختبار');
    }

    public function test_student_without_access_cannot_see_protected_delivery_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $lecture = Lecture::query()->firstWhere('slug', 'newton-laws-core');

        $this->actingAs($student, 'student');

        $this->get(route('student.lectures.show', $lecture))
            ->assertOk()
            ->assertDontSeeText('الفيديو الرئيسي لقوانين نيوتن')
            ->assertSeeText('استعرض الباقات المرتبطة');
    }

    public function test_opening_accessible_lecture_creates_progress_row(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $lecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');

        $this->actingAs($student, 'student');

        $this->get(route('student.lectures.show', $lecture))->assertOk();

        $this->assertDatabaseHas('lecture_progress', [
            'student_id' => $student->id,
            'lecture_id' => $lecture->id,
        ]);
    }

    public function test_posting_progress_updates_stores_resume_position_and_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $lecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');

        $this->actingAs($student, 'student');
        $this->get(route('student.lectures.show', $lecture))->assertOk();

        $this->postJson(route('student.lectures.progress.update', $lecture), [
            'position_seconds' => 600,
            'consumed_seconds' => 600,
        ])->assertOk();

        $this->postJson(route('student.lectures.progress.update', $lecture), [
            'position_seconds' => 300,
            'consumed_seconds' => 300,
        ])->assertOk();

        $progress = LectureProgress::query()
            ->where('student_id', $student->id)
            ->where('lecture_id', $lecture->id)
            ->firstOrFail();

        $this->assertSame(1, LectureProgress::query()
            ->where('student_id', $student->id)
            ->where('lecture_id', $lecture->id)
            ->count());
        $this->assertSame(300, (int) $progress->last_position_seconds);
        $this->assertSame(600, (int) $progress->consumed_seconds);
    }

    public function test_completion_threshold_marks_lecture_as_completed(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $lecture = Lecture::query()->firstWhere('slug', 'foundation-kinematics-free');

        $this->actingAs($student, 'student');
        $this->get(route('student.lectures.show', $lecture))->assertOk();

        $this->postJson(route('student.lectures.progress.update', $lecture), [
            'position_seconds' => 1800,
            'consumed_seconds' => 1800,
        ])->assertOk();

        $progress = LectureProgress::query()
            ->where('student_id', $student->id)
            ->where('lecture_id', $lecture->id)
            ->firstOrFail();

        $this->assertNotNull($progress->completed_at);
        $this->assertGreaterThanOrEqual(90, (float) $progress->completion_percent);
    }

    public function test_student_catalog_shows_progress_state_for_authenticated_student(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::query()->firstWhere('email', 'student@example.edu');

        $this->actingAs($student, 'student');

        $this->get(route('student.lectures.index', ['tab' => 'lecture']))
            ->assertOk()
            ->assertSeeText('مدخل مجاني إلى الحركة')
            ->assertSeeText('مكتمل')
            ->assertSeeText('قوانين نيوتن الأساسية')
            ->assertSeeText('43% مكتمل');
    }
}
