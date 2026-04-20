<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureProgress;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LectureProgress>
 */
class LectureProgressFactory extends Factory
{
    protected $model = LectureProgress::class;

    public function definition(): array
    {
        $percent = fake()->numberBetween(5, 100);
        $startedAt = now()->subDays(fake()->numberBetween(1, 7));
        $lastOpenedAt = (clone $startedAt)->addHours(fake()->numberBetween(1, 12));

        return [
            'student_id' => Student::factory(),
            'lecture_id' => Lecture::factory(),
            'started_at' => $startedAt,
            'first_opened_at' => $startedAt,
            'last_opened_at' => $lastOpenedAt,
            'last_position_seconds' => fake()->numberBetween(30, 2400),
            'consumed_seconds' => fake()->numberBetween(30, 2400),
            'completion_percent' => $percent,
            'completed_at' => $percent >= 90 ? $lastOpenedAt : null,
            'last_checkpoint_id' => null,
            'meta' => null,
        ];
    }
}
