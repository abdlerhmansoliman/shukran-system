<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create basic permissions
        $resources = [
            'users',
            'roles',
            'customers',
            'packages',
            'employees',
            'groups',
            'categories',
            'levels',
            'discounts',
        ];

        $permissions = ['view reports', 'manage attendance']; // Keep custom non-resource permissions here

        foreach ($resources as $resource) {
            $permissions[] = "view $resource";
            $permissions[] = "create $resource";
            $permissions[] = "edit $resource";
            $permissions[] = "delete $resource";
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Employee role and assign default view/create permissions
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);
        $employeePermissions = [];
        foreach ($resources as $resource) {
            if (!in_array($resource, ['users', 'roles'])) { // Employees shouldn't manage users/roles
                $employeePermissions[] = "view $resource";
                $employeePermissions[] = "create $resource";
            }
        }
        $employeeRole->givePermissionTo($employeePermissions);

        // Create Data Entry role and assign very specific permissions
        $dataEntryRole = Role::firstOrCreate(['name' => 'Data Entry']);
        $dataEntryRole->givePermissionTo([
            'view customers',
            'create customers',
            'edit customers',
        ]);

        // Assign Admin role to user ID 1 if exists
        $user = User::find(1);
        if ($user) {
            $user->assignRole('Admin');
        }
    }
}
