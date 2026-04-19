<?php

namespace Database\Factories;

use App\Modules\Centers\Models\EducationalCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationalCenter>
 */
class EducationalCenterFactory extends Factory
{
    protected $model = EducationalCenter::class;

    public function definition(): array
    {
        return [
            'name_ar' => fake()->randomElement(['سنتر الإتقان - مدينة نصر', 'سنتر الإتقان - الهرم']),
            'name_en' => null,
            'city' => fake('ar_EG')->city(),
            'is_active' => true,
        ];
    }
}
