<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\ExamQuestion;
use App\Modules\Academic\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamQuestion>
 */
class ExamQuestionFactory extends Factory
{
    protected $model = ExamQuestion::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'question_id' => Question::factory(),
            'sort_order' => fake()->numberBetween(1, 10),
            'max_score' => fake()->numberBetween(1, 3),
        ];
    }
}
