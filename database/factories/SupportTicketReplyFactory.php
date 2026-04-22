<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketReply;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicketReply>
 */
class SupportTicketReplyFactory extends Factory
{
    protected $model = SupportTicketReply::class;

    public function definition(): array
    {
        $student = Student::factory()->create();

        return [
            'support_ticket_id' => SupportTicket::factory(),
            'author_type' => $student->getMorphClass(),
            'author_id' => $student->id,
            'body' => fake('ar_EG')->paragraph(),
            'is_staff_reply' => false,
        ];
    }
}
