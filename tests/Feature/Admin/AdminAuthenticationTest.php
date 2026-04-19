<?php

namespace Tests\Feature\Admin;

use App\Modules\Identity\Models\Admin;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_is_accessible(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('تسجيل دخول الإدارة');
    }

    public function test_admin_can_authenticate_with_valid_credentials(): void
    {
        $this->seed(PermissionSeeder::class);

        $admin = Admin::factory()->create([
            'email' => 'admin@example.edu',
            'password' => 'password',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}
