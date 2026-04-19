<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentStatusHistory;
use App\Shared\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentStatusHistory>
 */
class StudentStatusHistoryFactory extends Factory
{
    protected $model = StudentStatusHistory::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'previous_status' => null,
            'new_status' => StudentStatus::Pending,
            'reason' => fake('ar_EG')->sentence(),
            'created_at' => now(),
        ];
    }
}
