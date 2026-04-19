<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['علمي علوم', 'علمي رياضة', 'أزهر']);

        return [
            'grade_id' => Grade::factory(),
            'name_ar' => $name,
            'name_en' => $name,
            'code' => fake()->unique()->lexify('track-???'),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
