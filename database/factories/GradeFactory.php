<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stage = fake()->randomElement(['الأول', 'الثاني', 'الثالث']);

        return [
            'name_ar' => "الصف الثانوي {$stage}",
            'name_en' => "Secondary {$stage}",
            'code' => fake()->unique()->lexify('grade-???'),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
