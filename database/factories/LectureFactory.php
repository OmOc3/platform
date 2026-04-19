<?php

namespace Database\Factories;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use App\Modules\Commerce\Models\Product;
use App\Shared\Enums\ContentKind;
use App\Shared\Enums\ProductKind;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lecture>
 */
class LectureFactory extends Factory
{
    protected $model = Lecture::class;

    public function definition(): array
    {
        $title = fake('ar_EG')->randomElement([
            'قوانين نيوتن وتطبيقاتها',
            'مراجعة الكهربية الساكنة',
            'أفكار حل مسائل الحركة',
            'مراجعة الباب الأول',
        ]);

        return [
            'product_id' => Product::factory()->state([
                'kind' => ProductKind::Lecture,
                'name_ar' => $title,
                'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(10, 99),
                'price_amount' => fake()->numberBetween(0, 250),
            ]),
            'grade_id' => Grade::factory(),
            'track_id' => Track::factory(),
            'curriculum_section_id' => CurriculumSection::factory(),
            'lecture_section_id' => LectureSection::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'short_description' => fake('ar_EG')->sentence(),
            'long_description' => fake('ar_EG')->paragraphs(2, true),
            'thumbnail_url' => null,
            'type' => fake()->randomElement([ContentKind::Lecture, ContentKind::Review]),
            'price_amount' => fake()->numberBetween(0, 250),
            'currency' => 'EGP',
            'duration_minutes' => fake()->numberBetween(25, 95),
            'is_active' => true,
            'is_featured' => fake()->boolean(35),
            'is_free' => false,
            'published_at' => now()->subDays(fake()->numberBetween(1, 15)),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => null,
        ];
    }
}
