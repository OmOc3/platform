<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureCheckpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LectureCheckpoint>
 */
class LectureCheckpointFactory extends Factory
{
    protected $model = LectureCheckpoint::class;

    public function definition(): array
    {
        return [
            'lecture_id' => Lecture::factory(),
            'title' => fake('ar_EG')->sentence(3),
            'position_seconds' => fake()->numberBetween(60, 1200),
            'sort_order' => fake()->numberBetween(1, 5),
            'is_required' => true,
            'metadata' => null,
        ];
    }
}
