<div class="flex items-center justify-end gap-2 whitespace-nowrap">
    <a
        href="{{ route('levels.edit', $level) }}"
        class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
    >
        {{ __('Edit') }}
    </a>

    <form action="{{ route('levels.destroy', $level) }}" method="POST" class="inline-block">
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
            onclick="return confirm('{{ __('Delete this level?') }}')"
        >
            {{ __('Delete') }}
        </button>
    </form>
</div>
