<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = Admin::query()->firstOrCreate(
            ['email' => env('PLATFORM_SUPER_ADMIN_EMAIL', 'owner@example.edu')],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'مالك المنصة',
                'phone' => '01000000000',
                'job_title' => 'Super Admin',
                'locale' => 'ar',
                'is_active' => true,
                'password' => env('PLATFORM_SUPER_ADMIN_PASSWORD', 'password'),
            ],
        );

        $superAdmin->syncRoles(['Super Admin']);

        $academicManager = Admin::query()->firstOrCreate([
            'email' => 'academic.manager@example.edu',
        ], [
            'uuid' => (string) Str::uuid(),
            'name' => 'مدير أكاديمي',
            'phone' => '01000000001',
            'job_title' => 'Academic Manager',
            'locale' => 'ar',
            'is_active' => true,
            'password' => 'password',
        ]);

        $academicManager->syncRoles(['Academic Manager']);

        $supportAgent = Admin::query()->firstOrCreate([
            'email' => 'support.agent@example.edu',
        ], [
            'uuid' => (string) Str::uuid(),
            'name' => 'مسؤول دعم',
            'phone' => '01000000002',
            'job_title' => 'Support Agent',
            'locale' => 'ar',
            'is_active' => true,
            'password' => 'password',
        ]);

        $supportAgent->syncRoles(['Support Agent']);
    }
}
