<?php

namespace Database\Factories;

use App\Modules\Support\Enums\ForumAttachmentType;
use App\Modules\Support\Models\ForumAttachment;
use App\Modules\Support\Models\ForumMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForumAttachment>
 */
class ForumAttachmentFactory extends Factory
{
    protected $model = ForumAttachment::class;

    public function definition(): array
    {
        return [
            'forum_message_id' => ForumMessage::factory(),
            'type' => ForumAttachmentType::Image,
            'disk' => 'public',
            'path' => 'forum/demo-'.fake()->unique()->numberBetween(100, 999).'.jpg',
            'original_name' => 'attachment.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1024, 5024),
        ];
    }
}
