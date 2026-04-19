<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithAdminAuth;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_admin_manager_can_create_another_admin_with_role_assignment(): void
    {
        $this->signInAdmin(['admins.view', 'admins.manage']);

        $response = $this->post(route('admin.admins.store'), [
            'name' => 'مشرف جديد',
            'email' => 'new.admin@example.edu',
            'phone' => '01011111111',
            'job_title' => 'Coordinator',
            'locale' => 'ar',
            'is_active' => true,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_names' => ['Support Agent'],
        ]);

        $response->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseHas('admins', [
            'email' => 'new.admin@example.edu',
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'model_type' => 'App\\Modules\\Identity\\Models\\Admin',
        ]);
    }
}
