<div class="flex items-center justify-end gap-2 whitespace-nowrap">
    <a
        href="{{ route('groups.show', $group) }}"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
    >
        {{ __('View') }}
    </a>

    <a
        href="{{ route('groups.edit', $group) }}"
        class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
    >
        {{ __('Edit') }}
    </a>

    <form action="{{ route('groups.destroy', $group) }}" method="POST" class="inline-block">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
            onclick="return confirm('{{ __('Delete this group?') }}')"
        >
            {{ __('Delete') }}
        </button>
    </form>
</div>
