<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ads.approve', 'ads.reject', 'ads.delete-any',
            'users.manage', 'categories.manage', 'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::findOrCreate('user', 'web');
        Role::findOrCreate('moderator', 'web')->syncPermissions(
            Permission::whereIn('name', ['ads.approve', 'ads.reject', 'reports.view'])->get()
        );
        Role::findOrCreate('admin', 'web')->syncPermissions(Permission::all());

        $admin = User::firstOrCreate(
            ['email' => 'admin@sooqify.test' ],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                ]
        );
        $admin->assignRole('admin');
    }
}
