<?php

namespace Database\Factories;

use App\Modules\Centers\Models\AttendanceRecord;
use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Students\Models\Student;
use App\Shared\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'student_id' => Student::factory(),
            'attendance_status' => fake()->randomElement(AttendanceStatus::cases()),
            'exam_status_label' => fake()->randomElement(['لم يختبر بعد', 'تم الاختبار']),
            'score' => fake()->numberBetween(5, 20),
            'max_score' => 20,
            'notes' => null,
            'recorded_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }
}
