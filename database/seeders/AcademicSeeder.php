<?php

namespace Database\Seeders;

use App\Modules\Academic\Models\Grade;
use App\Modules\Academic\Models\Track;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $gradeOne = Grade::query()->firstOrCreate(
            ['code' => 'grade-1-secondary'],
            [
                'name_ar' => 'الصف الأول الثانوي',
                'name_en' => 'First Secondary',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        $gradeTwo = Grade::query()->firstOrCreate(
            ['code' => 'grade-2-secondary'],
            [
                'name_ar' => 'الصف الثاني الثانوي',
                'name_en' => 'Second Secondary',
                'sort_order' => 2,
                'is_active' => true,
            ],
        );

        $gradeThree = Grade::query()->firstOrCreate(
            ['code' => 'grade-3-secondary'],
            [
                'name_ar' => 'الصف الثالث الثانوي',
                'name_en' => 'Third Secondary',
                'sort_order' => 3,
                'is_active' => true,
            ],
        );

        foreach ([
            [$gradeTwo, 'science-track', 'علمي علوم', 'Science'],
            [$gradeTwo, 'math-track', 'علمي رياضة', 'Math'],
            [$gradeThree, 'thanaweya-general', 'عام', 'General'],
            [$gradeThree, 'azhar-track', 'أزهر', 'Azhar'],
        ] as [$grade, $code, $nameAr, $nameEn]) {
            Track::query()->firstOrCreate(
                ['code' => $code],
                [
                    'grade_id' => $grade->id,
                    'name_ar' => $nameAr,
                    'name_en' => $nameEn,
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            );
        }

        Track::query()->firstOrCreate(
            ['code' => 'foundation-track'],
            [
                'grade_id' => $gradeOne->id,
                'name_ar' => 'تأسيسي',
                'name_en' => 'Foundation',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
