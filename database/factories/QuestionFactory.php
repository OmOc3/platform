<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Question;
use App\Shared\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'type' => QuestionType::MultipleChoice,
            'prompt' => fake('ar_EG')->sentence(10),
            'explanation' => fake('ar_EG')->paragraph(),
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
