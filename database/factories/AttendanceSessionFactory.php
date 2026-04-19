<?php

namespace Database\Factories;

use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    protected $model = AttendanceSession::class;

    public function definition(): array
    {
        return [
            'group_id' => EducationalGroup::factory(),
            'title' => fake()->randomElement(['محاضرة الحركة الاهتزازية', 'مراجعة قوانين الكهرباء']),
            'session_type' => fake()->randomElement(['lecture', 'exam']),
            'starts_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }
}
