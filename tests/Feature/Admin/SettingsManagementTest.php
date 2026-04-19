<?php

namespace Tests\Feature\Admin;

use App\Modules\Identity\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_can_create_a_setting_and_audit_log_is_recorded(): void
    {
        $this->signInAdmin(['settings.view', 'settings.manage']);

        $response = $this->post(route('admin.settings.store'), [
            'group' => 'branding',
            'key' => 'homepage_cta',
            'label' => 'عنوان الدعوة',
            'description' => 'نسخة الصفحة الرئيسية',
            'type' => 'string',
            'value' => 'ابدأ الآن',
            'is_public' => true,
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'group' => 'branding',
            'key' => 'homepage_cta',
            'label' => 'عنوان الدعوة',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'identity.setting.created',
        ]);
    }

    public function test_settings_index_is_permission_protected(): void
    {
        $this->signInAdmin(['dashboard.view']);

        $response = $this->get(route('admin.settings.index'));

        $response->assertForbidden();
    }
}
