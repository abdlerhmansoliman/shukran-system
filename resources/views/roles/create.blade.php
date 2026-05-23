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
            
            <div class="mt-4">
                <h1 class="text-3xl font-semibold text-slate-900">{{ __('Add Role') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Create a new role and configure its permissions.') }}</p>
            </div>
        </div>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            
            @include('roles._form', [
                'role' => null,
                'submitLabel' => __('Create Role'),
                'cancelUrl' => route('roles.index'),
            ])
        </form>
    </div>
</div>
@endsection
