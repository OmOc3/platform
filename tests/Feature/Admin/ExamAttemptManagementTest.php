<?php

namespace Tests\Feature\Admin;

use App\Modules\Academic\Models\Exam;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\ExamAttemptService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class ExamAttemptManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_with_exams_view_can_inspect_attempts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()->where('slug', 'kinematics-quiz-open')->firstOrFail();

        /** @var ExamAttemptService $service */
        $service = app(ExamAttemptService::class);
        $attempt = $service->start([
            'student' => $student,
            'exam' => $exam,
        ]);

        $this->signInAdmin(['exams.view']);

        $this->get(route('admin.exam-attempts.index'))
            ->assertOk()
            ->assertSeeText($exam->title)
            ->assertSeeText($student->name);

        $this->get(route('admin.exam-attempts.show', $attempt))
            ->assertOk()
            ->assertSeeText($student->student_number);
    }

    public function test_admin_without_exams_view_cannot_inspect_attempts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()->where('slug', 'kinematics-quiz-open')->firstOrFail();

        /** @var ExamAttemptService $service */
        $service = app(ExamAttemptService::class);
        $attempt = $service->start([
            'student' => $student,
            'exam' => $exam,
        ]);

        $this->signInAdmin(['dashboard.view']);

        $this->get(route('admin.exam-attempts.index'))->assertForbidden();
        $this->get(route('admin.exam-attempts.show', $attempt))->assertForbidden();
    }
}
