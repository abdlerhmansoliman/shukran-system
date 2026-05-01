<div class="flex items-center justify-end gap-2 whitespace-nowrap">
    <a
        href="{{ route('employees.show', $employee) }}"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
    >
        {{ __('View') }}
    </a>

    <a
        href="{{ route('employees.edit', $employee) }}"
        class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
    >
        {{ __('Edit') }}
    </a>

    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-block">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
            onclick="return confirm('{{ __('Delete this employee?') }}')"
        >
            {{ __('Delete') }}
        </button>
    </form>
</div>
