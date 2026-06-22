<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('programs.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                </svg>
            </a>
            <h2 class="text-xl font-bold leading-tight tracking-tight text-slate-900">
                {{ __('Edit Program') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form action="{{ route('programs.update', $program) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                @include('programs._form', [
                    'program' => $program,
                    'submitLabel' => __('Save Changes'),
                    'cancelUrl' => route('programs.index')
                ])
            </form>
        </div>
    </div>
</x-app-layout>
