<?php

namespace Database\Factories;

use App\Modules\Academic\Models\Exam;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Lecture;
use App\Modules\Academic\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $title = fake('ar_EG')->randomElement([
            'اختبار تقويمي سريع',
            'اختبار المراجعة الأسبوعية',
            'اختبار الباب الأول',
        ]);

        return [
            'lecture_id' => Lecture::factory(),
            'grade_id' => Grade::factory(),
            'track_id' => Track::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'short_description' => fake('ar_EG')->sentence(),
            'long_description' => fake('ar_EG')->paragraph(),
            'thumbnail_url' => null,
            'price_amount' => 0,
            'currency' => 'EGP',
            'duration_minutes' => fake()->numberBetween(10, 60),
            'question_count' => fake()->numberBetween(5, 20),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'is_free' => true,
            'published_at' => now()->subDays(fake()->numberBetween(1, 12)),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => null,
        ];
    }
}
