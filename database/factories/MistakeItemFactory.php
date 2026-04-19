<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Students\Models\MistakeItem;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MistakeItem>
 */
class MistakeItemFactory extends Factory
{
    protected $model = MistakeItem::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'lecture_id' => Lecture::factory(),
            'exam_id' => Exam::factory(),
            'question_reference' => 'Q-'.fake()->unique()->numberBetween(100, 999),
            'question_text' => fake('ar_EG')->paragraph(),
            'correct_answer_snapshot' => fake('ar_EG')->sentence(),
            'model_answer_snapshot' => fake('ar_EG')->paragraph(),
            'explanation' => fake('ar_EG')->paragraph(),
            'image_path' => null,
            'score_lost' => fake()->numberBetween(1, 5),
            'score_meta' => ['max_score' => 20],
            'source' => 'seeded_demo',
            'meta' => null,
        ];
    }
}
