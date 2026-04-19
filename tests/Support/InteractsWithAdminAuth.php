<?php

namespace Tests\Support;

use App\Modules\Identity\Models\Admin;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;

trait InteractsWithAdminAuth
{
    protected function signInAdmin(array $permissions = ['dashboard.view']): Admin
    {
        $this->seed(PermissionSeeder::class);

        $admin = Admin::factory()->create();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        $admin->givePermissionTo($permissions);

        $this->actingAs($admin, 'admin');

        return $admin;
    }
}
