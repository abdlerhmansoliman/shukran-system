<div class="space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Role Information') }}</p>

        <div class="mt-6">
            <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Role Name') }}</label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                value="{{ old('name', $role?->name) }}" 
                @if($role && in_array($role->name, ['Admin'])) readonly @endif
                required 
                class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10 @if($role && in_array($role->name, ['Admin'])) bg-slate-50 text-slate-500 cursor-not-allowed @endif"
            >
            @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            @if($role && in_array($role->name, ['Admin']))
                <p class="mt-2 text-xs text-slate-500">{{ __('The Admin role name cannot be modified.') }}</p>
            @endif
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Permissions') }}</p>
        <p class="mt-2 text-sm text-slate-500">{{ __('Select the permissions you want to assign to this role.') }}</p>

        @if($role && $role->name === 'Admin')
            <div class="mt-6 rounded-xl bg-slate-50 p-4 border border-slate-200">
                <p class="text-sm font-medium text-slate-700">{{ __('The Admin role automatically has all permissions. They cannot be modified.') }}</p>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($groupedPermissions as $group => $permissions)
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900">{{ ucfirst($group) }}</h3>
                        <div class="space-y-3">
                            @foreach($permissions as $permission)
                                <label class="flex items-start gap-3">
                                    <div class="flex h-5 items-center">
                                        <input 
                                            type="checkbox" 
                                            name="permissions[]" 
                                            value="{{ $permission->name }}" 
                                            @checked(in_array($permission->name, old('permissions', $rolePermissions)))
                                            class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                        >
                                    </div>
                                    <div class="text-sm">
                                        <span class="font-medium text-slate-700">{{ ucwords($permission->name) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions')<p class="mt-4 text-sm text-rose-600">{{ $message }}</p>@enderror
        @endif
    </div>

    <div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ $cancelUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
            {{ $submitLabel }}
        </button>
    </div>
</div>
