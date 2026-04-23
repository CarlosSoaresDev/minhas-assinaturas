<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create basic permissions
        $manageUsers = Permission::firstOrCreate(['name' => 'manage users']);
        $manageSubscriptions = Permission::firstOrCreate(['name' => 'manage subscriptions']);

        // Assign
        $adminRole->givePermissionTo($manageUsers);
        $userRole->givePermissionTo($manageSubscriptions);
    }
}
