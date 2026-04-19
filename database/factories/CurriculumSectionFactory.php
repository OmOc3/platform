<?php

namespace Database\Factories;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CurriculumSection>
 */
class CurriculumSectionFactory extends Factory
{
    protected $model = CurriculumSection::class;

    public function definition(): array
    {
        $name = fake('ar_EG')->randomElement([
            'الحركة الخطية',
            'الكهرباء الحديثة',
            'الشغل والطاقة',
            'الموجات والضوء',
        ]);

        return [
            'grade_id' => Grade::factory(),
            'track_id' => Track::factory(),
            'name_ar' => $name,
            'name_en' => Str::headline($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 99),
            'description' => fake('ar_EG')->sentence(),
            'sort_order' => fake()->numberBetween(1, 12),
            'is_active' => true,
        ];
    }
}
