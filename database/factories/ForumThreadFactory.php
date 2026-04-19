<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ForumThreadStatus;
use App\Modules\Support\Enums\ForumVisibility;
use App\Modules\Support\Models\ForumThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumThread>
 */
class ForumThreadFactory extends Factory
{
    protected $model = ForumThread::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'title' => fake('ar_EG')->sentence(4),
            'status' => ForumThreadStatus::Open,
            'visibility' => ForumVisibility::Public,
            'last_activity_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'answered_at' => null,
            'closed_at' => null,
        ];
    }
}
