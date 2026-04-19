<?php

namespace Tests\Feature;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use App\Shared\Contracts\ExamAttemptService;
use App\Shared\Enums\ExamAttemptStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_with_access_can_start_exam_attempt(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()->where('slug', 'kinematics-quiz-open')->firstOrFail();

        $this->actingAs($student, 'student');

        $this->post(route('student.exam-attempts.start', $exam))
            ->assertRedirect();

        $attempt = ExamAttempt::query()->where('student_id', $student->id)->where('exam_id', $exam->id)->first();

        $this->assertNotNull($attempt);
        $this->assertSame(ExamAttemptStatus::InProgress, $attempt->status);
        $this->assertSame($exam->question_count, $attempt->total_questions);
    }

    public function test_student_without_access_cannot_start_paid_exam_attempt(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()->where('slug', 'newton-laws-weekly-quiz')->firstOrFail();

        $this->actingAs($student, 'student');

        $this->from(route('student.lectures.exams.show', $exam))
            ->post(route('student.exam-attempts.start', $exam))
            ->assertRedirect(route('student.lectures.exams.show', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertDatabaseMissing('exam_attempts', [
            'student_id' => $student->id,
            'exam_id' => $exam->id,
        ]);
    }

    public function test_inactive_or_questionless_exam_cannot_be_started(): void
    {
        $student = Student::factory()->create();
        $inactiveExam = Exam::factory()->create([
            'is_free' => true,
            'price_amount' => 0,
            'is_active' => false,
        ]);
        $questionlessExam = Exam::factory()->create([
            'is_free' => true,
            'price_amount' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($student, 'student');

        $this->from(route('student.lectures.index'))
            ->post(route('student.exam-attempts.start', $inactiveExam))
            ->assertRedirect(route('student.lectures.index'))
            ->assertSessionHasErrors('exam');

        $this->from(route('student.lectures.index'))
            ->post(route('student.exam-attempts.start', $questionlessExam))
            ->assertRedirect(route('student.lectures.index'))
            ->assertSessionHasErrors('exam');
    }

    public function test_student_can_save_progress_resume_and_submit_objective_exam(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()
            ->where('slug', 'kinematics-quiz-open')
            ->with('examQuestions.question.choices')
            ->firstOrFail();

        $this->actingAs($student, 'student');
        $this->post(route('student.exam-attempts.start', $exam))->assertRedirect();

        $attempt = ExamAttempt::query()->where('student_id', $student->id)->where('exam_id', $exam->id)->firstOrFail();
        $questions = $exam->examQuestions->values();

        $savePayload = [
            'answers' => [
                $questions[0]->question_id => $questions[0]->question->choices->firstWhere('is_correct', true)->id,
            ],
        ];

        $this->post(route('student.exam-attempts.save', $attempt), $savePayload)
            ->assertRedirect(route('student.exam-attempts.show', $attempt));

        $this->get(route('student.exam-attempts.show', $attempt))
            ->assertOk()
            ->assertSeeText('إرسال الاختبار');

        $submitPayload = [
            'answers' => [
                $questions[0]->question_id => $questions[0]->question->choices->firstWhere('is_correct', true)->id,
                $questions[1]->question_id => $questions[1]->question->choices->firstWhere('is_correct', true)->id,
                $questions[2]->question_id => $questions[2]->question->choices->firstWhere('is_correct', false)->id,
            ],
        ];

        $this->post(route('student.exam-attempts.submit', $attempt), $submitPayload)
            ->assertRedirect(route('student.exam-attempts.result', $attempt));

        $attempt->refresh();

        $this->assertSame(ExamAttemptStatus::Graded, $attempt->status);
        $this->assertSame(3, $attempt->total_questions);
        $this->assertSame(3, $attempt->answered_questions);
        $this->assertSame(2, $attempt->correct_answers_count);
        $this->assertSame(2, $attempt->total_score);
        $this->assertSame(3, $attempt->max_score);
        $this->assertSame(1, data_get($attempt->result_meta, 'wrong_count'));
        $this->assertSame(0, data_get($attempt->result_meta, 'skipped_count'));

        $this->assertDatabaseHas('mistake_items', [
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'source' => 'exam_attempt',
        ]);
    }

    public function test_repeated_submit_is_safe_and_does_not_duplicate_mistakes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $student = Student::factory()->create();
        $exam = Exam::query()
            ->where('slug', 'kinematics-quiz-open')
            ->with('examQuestions.question.choices')
            ->firstOrFail();

        $this->actingAs($student, 'student');
        $this->post(route('student.exam-attempts.start', $exam))->assertRedirect();

        $attempt = ExamAttempt::query()->where('student_id', $student->id)->where('exam_id', $exam->id)->firstOrFail();
        $wrongChoice = $exam->examQuestions->first()->question->choices->firstWhere('is_correct', false);

        $payload = [
            'answers' => [
                $exam->examQuestions->first()->question_id => $wrongChoice->id,
            ],
        ];

        $this->post(route('student.exam-attempts.submit', $attempt), $payload)->assertRedirect();
        $this->post(route('student.exam-attempts.submit', $attempt->fresh()), $payload)->assertRedirect();

        $this->assertSame(1, MistakeItem::query()
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->where('source', 'exam_attempt')
            ->count());
    }

    public function test_expired_attempt_is_auto_submitted_on_open(): void
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

        $attempt->forceFill([
            'started_at' => now()->subMinutes(($exam->duration_minutes ?? 1) + 1),
        ])->save();

        $this->actingAs($student, 'student');

        $this->get(route('student.exam-attempts.show', $attempt))
            ->assertRedirect(route('student.exam-attempts.result', $attempt));

        $attempt->refresh();

        $this->assertSame(ExamAttemptStatus::Graded, $attempt->status);
        $this->assertTrue((bool) data_get($attempt->result_meta, 'submitted_by_timer'));
    }

    public function test_student_cannot_view_another_students_attempt(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = Student::factory()->create();
        $other = Student::factory()->create();
        $exam = Exam::query()->where('slug', 'kinematics-quiz-open')->firstOrFail();

        /** @var ExamAttemptService $service */
        $service = app(ExamAttemptService::class);
        $attempt = $service->start([
            'student' => $owner,
            'exam' => $exam,
        ]);

        $this->actingAs($other, 'student');

        $this->get(route('student.exam-attempts.show', $attempt))->assertForbidden();
        $this->get(route('student.exam-attempts.result', $attempt))->assertForbidden();
    }
}
