@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                </svg>
                {{ __('Back to Roles') }}
            </a>
            
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-slate-900">{{ __('Edit Role') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Modify role name and adjust its permissions.') }}</p>
                </div>
                
                <div class="flex items-center gap-2">
                    @can('delete roles')
                        @if(!in_array($role->name, ['Admin', 'Employee', 'Data Entry']))
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                    {{ __('Delete Role') }}
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            
            @include('roles._form', [
                'role' => $role,
                'submitLabel' => __('Save Changes'),
                'cancelUrl' => route('roles.index'),
            ])
        </form>
    </div>
</div>
@endsection
