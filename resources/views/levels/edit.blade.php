@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Level Catalog') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Edit Level') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Update the placement level name for :name.', ['name' => $level->name]) }}</p>
            </div>

            <a
                href="{{ route('levels.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                {{ __('Back to Levels') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please review the highlighted fields and try again.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('levels.update', $level) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('levels._form', [
                'level' => $level,
                'submitLabel' => __('Update Level'),
                'cancelUrl' => route('levels.index'),
            ])
        </form>
    </div>
</div>
@endsection
