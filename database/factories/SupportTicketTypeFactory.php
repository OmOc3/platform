<?php

namespace Database\Factories;

use App\Modules\Support\Models\SupportTeam;
use App\Modules\Support\Models\SupportTicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketType>
 */
class SupportTicketTypeFactory extends Factory
{
    protected $model = SupportTicketType::class;

    public function definition(): array
    {
        return [
            'default_team_id' => SupportTeam::factory(),
            'name' => fake('ar_EG')->sentence(3),
            'description' => fake('ar_EG')->sentence(),
            'is_active' => true,
        ];
    }
}
