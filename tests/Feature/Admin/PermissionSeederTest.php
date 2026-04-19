<?php

namespace Tests\Feature\Admin;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_seeder_creates_roles_and_permissions(): void
    {
        $this->seed(PermissionSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'settings.manage',
            'guard_name' => 'admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Super Admin',
            'guard_name' => 'admin',
        ]);

        $this->assertGreaterThan(0, Role::findByName('Super Admin', 'admin')->permissions()->count());
    }
}
