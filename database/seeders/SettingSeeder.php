<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            [
                'group' => 'branding',
                'key' => 'teacher_name',
                'label' => 'اسم المدرس',
                'type' => 'string',
                'value' => config('platform.brand.teacher_name'),
            ],
            [
                'group' => 'branding',
                'key' => 'tagline',
                'label' => 'العبارة التعريفية',
                'type' => 'text',
                'value' => config('platform.brand.tagline'),
            ],
            [
                'group' => 'support',
                'key' => 'support_phone',
                'label' => 'هاتف الدعم',
                'type' => 'string',
                'value' => config('platform.brand.support_phone'),
            ],
            [
                'group' => 'support',
                'key' => 'support_whatsapp',
                'label' => 'واتساب الدعم',
                'type' => 'string',
                'value' => config('platform.brand.support_whatsapp'),
            ],
        ] as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting + ['is_public' => true],
            );
        }
    }
}
