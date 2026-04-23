<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketType;
use App\Shared\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'support_ticket_type_id' => SupportTicketType::factory(),
            'support_team_id' => null,
            'assigned_admin_id' => null,
            'subject' => fake('ar_EG')->sentence(5),
            'status' => TicketStatus::Open,
            'last_activity_at' => now()->subHours(fake()->numberBetween(1, 72)),
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }
}
