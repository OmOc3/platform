<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\ExamAttemptStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'status' => ExamAttemptStatus::InProgress,
            'started_at' => now()->subMinutes(fake()->numberBetween(1, 20)),
            'submitted_at' => null,
            'graded_at' => null,
            'total_questions' => 0,
            'answered_questions' => 0,
            'correct_answers_count' => null,
            'total_score' => null,
            'max_score' => null,
            'attempt_number' => 1,
            'time_limit_snapshot' => 15,
            'result_meta' => null,
        ];
    }
}
