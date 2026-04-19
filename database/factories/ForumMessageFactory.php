<?php

namespace Database\Factories;

use App\Modules\Students\Models\Student;
use App\Modules\Support\Models\ForumMessage;
use App\Modules\Support\Models\ForumThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumMessage>
 */
class ForumMessageFactory extends Factory
{
    protected $model = ForumMessage::class;

    public function definition(): array
    {
        $student = Student::factory()->create();

        return [
            'forum_thread_id' => ForumThread::factory(),
            'author_type' => $student->getMorphClass(),
            'author_id' => $student->id,
            'body' => fake('ar_EG')->paragraph(),
            'is_staff_reply' => false,
        ];
    }
}
