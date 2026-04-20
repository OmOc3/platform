<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureAsset;
use App\Shared\Enums\LectureAssetKind;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LectureAsset>
 */
class LectureAssetFactory extends Factory
{
    protected $model = LectureAsset::class;

    public function definition(): array
    {
        return [
            'lecture_id' => Lecture::factory(),
            'kind' => fake()->randomElement([
                LectureAssetKind::EmbedVideo,
                LectureAssetKind::ExternalVideo,
                LectureAssetKind::AttachmentLink,
                LectureAssetKind::ResourceLink,
                LectureAssetKind::TextBlock,
            ]),
            'title' => fake('ar_EG')->sentence(3),
            'url' => fake()->url(),
            'body' => fake('ar_EG')->paragraph(),
            'sort_order' => fake()->numberBetween(1, 4),
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
