<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Question;
use App\Modules\Academic\Models\QuestionChoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionChoice>
 */
class QuestionChoiceFactory extends Factory
{
    protected $model = QuestionChoice::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'content' => fake('ar_EG')->sentence(6),
            'is_correct' => false,
            'sort_order' => fake()->numberBetween(1, 4),
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
