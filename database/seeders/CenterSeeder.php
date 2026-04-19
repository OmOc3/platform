<?php

namespace Database\Seeders;

use App\Modules\Centers\Models\AttendanceSession;
use App\Modules\Centers\Models\EducationalCenter;
use App\Modules\Centers\Models\EducationalGroup;
use Illuminate\Database\Seeder;

class CenterSeeder extends Seeder
{
    public function run(): void
    {
        $center = EducationalCenter::query()->firstOrCreate(
            ['name_ar' => 'سنتر الإتقان - مدينة نصر'],
            [
                'name_en' => null,
                'city' => 'القاهرة',
                'is_active' => true,
            ],
        );

        $group = EducationalGroup::query()->firstOrCreate(
            [
                'center_id' => $center->id,
                'name_ar' => 'مجموعة الثلاثاء',
            ],
            [
                'name_en' => null,
                'schedule_note' => 'الثلاثاء والجمعة - 6 مساءً',
                'is_active' => true,
            ],
        );

        foreach ([
            ['محاضرة قوانين نيوتن', 'lecture', now()->subDays(10)],
            ['اختبار الكهرباء', 'exam', now()->subDays(3)],
        ] as [$title, $type, $date]) {
            AttendanceSession::query()->firstOrCreate(
                [
                    'group_id' => $group->id,
                    'title' => $title,
                ],
                [
                    'session_type' => $type,
                    'starts_at' => $date,
                ],
            );
        }
    }
}
