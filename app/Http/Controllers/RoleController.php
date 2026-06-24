<?php

namespace App\Http\Controllers;

use App\DataTables\RoleDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoleDataTable $datatable)
    {
        Gate::authorize('view roles');

        return $datatable->render('roles.index');
    }

    public function create()
    {
        Gate::authorize('create roles');

        return view('roles.create', $this->formData());
    }

    public function store(Request $request)
    {
        Gate::authorize('create roles');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', __('Role created successfully.'));
    }

    public function edit(Role $role)
    {
        Gate::authorize('edit roles');

        return view('roles.edit', [
            'role' => $role,
            ...$this->formData($role),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('edit roles');

        if (in_array($role->name, ['Admin'])) {
            return back()->with('error', __('The Admin role cannot be modified.'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('roles.edit', $role)
            ->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role)
    {
        Gate::authorize('delete roles');

        if (in_array($role->name, ['Admin', 'Employee', 'Data Entry'])) {
            return back()->with('error', __('System default roles cannot be deleted.'));
        }

        if ($role->users()->exists()) {
            return back()->with('error', __('Role cannot be deleted while assigned to users.'));
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', __('Role deleted successfully.'));
    }

    private function formData(?Role $role = null): array
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = [];

        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            $resource = count($parts) > 1 ? $parts[1] : 'general';

            if (! isset($groupedPermissions[$resource])) {
                $groupedPermissions[$resource] = [];
            }
            $groupedPermissions[$resource][] = $permission;
        }

        return [
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $role ? $role->permissions->pluck('name')->toArray() : [],
        ];
    }
}
