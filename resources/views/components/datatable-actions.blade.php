<div class="flex items-center justify-end gap-2 whitespace-nowrap">
    <a
        href="{{ route('customers.show', $customer->id) }}"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
    >
        View
    </a>

    @if(Route::has('customers.edit'))
        <a
            href="{{ route('customers.edit', $customer->id) }}"
            class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
        >
            Edit
        </a>
    @endif

    @if(Route::has('customers.destroy'))
        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline-block">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                onclick="return confirm('Delete this customer?')"
            >
                Delete
            </button>
        </form>
    @endif
</div>
