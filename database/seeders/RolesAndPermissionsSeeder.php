<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Alumni permissions
            'alumni:list',
            'alumni:view',
            'alumni:create',
            'alumni:edit',
            'alumni:delete',
            
            // User management permissions
            'user:list',
            'user:view',
            'user:create',
            'user:edit',
            'user:delete',
            
            // Role management permissions
            'role:list',
            'role:view',
            'role:create',
            'role:edit',
            'role:delete',
            
            // System management permissions
            'system:view-logs',
            'system:settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Super Admin
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'alumni:list', 'alumni:view', 'alumni:create', 'alumni:edit', 'alumni:delete',
            'user:list', 'user:view', 'user:create', 'user:edit',
            'role:list', 'role:view',
            'system:view-logs',
        ]);

        // Editor
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'alumni:list', 'alumni:view', 'alumni:create', 'alumni:edit',
        ]);

        // Viewer
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'alumni:list', 'alumni:view',
        ]);

        // Alumni
        $alumni = Role::create(['name' => 'alumni']);
        $alumni->givePermissionTo([
            'alumni:list', 'alumni:view', 'alumni:edit'
        ]);
    }
}
