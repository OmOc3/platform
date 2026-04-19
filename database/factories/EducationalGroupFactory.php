<?php

namespace Database\Factories;

use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationalGroup>
 */
class EducationalGroupFactory extends Factory
{
    protected $model = EducationalGroup::class;

    public function definition(): array
    {
        return [
            'center_id' => EducationalCenter::factory(),
            'name_ar' => fake()->randomElement(['مجموعة الثلاثاء', 'مجموعة الخميس']),
            'name_en' => null,
            'schedule_note' => fake('ar_EG')->sentence(),
            'is_active' => true,
        ];
    }
}
