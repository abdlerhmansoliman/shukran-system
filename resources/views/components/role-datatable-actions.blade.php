<div class="flex items-center justify-end gap-2">
    @can('edit roles')
        <a 
            href="{{ route('roles.edit', $role) }}" 
            class="inline-flex h-8 items-center justify-center rounded-lg bg-slate-50 px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
            title="{{ __('Edit Role') }}"
        >
            {{ __('Edit') }}
        </a>
    @endcan

    @can('delete roles')
        @if(!in_array($role->name, ['Admin', 'Employee', 'Data Entry']))
            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}');">
                @csrf
                @method('DELETE')
                <button 
                    type="submit" 
                    class="inline-flex h-8 items-center justify-center rounded-lg bg-rose-50 px-3 text-xs font-medium text-rose-700 transition hover:bg-rose-100"
                    title="{{ __('Delete Role') }}"
                >
                    {{ __('Delete') }}
                </button>
            </form>
        @endif
    @endcan
</div>
