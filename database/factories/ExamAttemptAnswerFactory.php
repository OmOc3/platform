<?php

namespace Database\Factories;

use App\Modules\Academic\Models\ExamAttempt;
use App\Modules\Academic\Models\ExamAttemptAnswer;
use App\Modules\Academic\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttemptAnswer>
 */
class ExamAttemptAnswerFactory extends Factory
{
    protected $model = ExamAttemptAnswer::class;

    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_answer' => (string) fake()->numberBetween(1, 4),
            'answer_payload' => null,
            'is_correct' => null,
            'awarded_score' => null,
            'max_score' => 1,
            'answer_meta' => null,
        ];
    }
}
