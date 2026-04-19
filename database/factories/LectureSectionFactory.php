<?php

namespace Database\Factories;

use App\Modules\Academic\Models\CurriculumSection;
use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\LectureSection;
use App\Modules\Academic\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LectureSection>
 */
class LectureSectionFactory extends Factory
{
    protected $model = LectureSection::class;

    public function definition(): array
    {
        $name = fake('ar_EG')->randomElement([
            'المحاضرات التأسيسية',
            'تمارين المستوى الأول',
            'مراجعة النصف الثاني',
            'الأسئلة الشائعة',
        ]);

        return [
            'grade_id' => Grade::factory(),
            'track_id' => Track::factory(),
            'curriculum_section_id' => CurriculumSection::factory(),
            'name_ar' => $name,
            'name_en' => Str::headline($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 99),
            'description' => fake('ar_EG')->sentence(),
            'sort_order' => fake()->numberBetween(1, 12),
            'is_active' => true,
        ];
    }
}
