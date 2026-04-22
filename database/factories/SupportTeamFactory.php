<?php

namespace Database\Factories;

use App\Modules\Support\Models\SupportTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTeam>
 */
class SupportTeamFactory extends Factory
{
    protected $model = SupportTeam::class;

    public function definition(): array
    {
        return [
            'name' => fake('ar_EG')->company(),
            'description' => fake('ar_EG')->sentence(),
            'is_active' => true,
        ];
    }
}
