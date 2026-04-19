<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Enums\ComplaintType;
use App\Modules\Support\Models\Complaint;
use App\Shared\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'type' => fake()->randomElement(ComplaintType::cases()),
            'status' => ComplaintStatus::Open,
            'content' => fake('ar_EG')->paragraph(),
            'admin_notes' => null,
            'resolved_at' => null,
        ];
    }
}
