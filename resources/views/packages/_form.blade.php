@php
    $statusValue = old('status', $package?->status ?? \App\Enums\PackageStatus::Active->value);
@endphp

<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Product Details') }}</p>

    <div class="mt-6 grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $package?->name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
            <select id="status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="program_id" class="text-sm font-semibold text-slate-700">{{ __('Program') }}</label>
            <select id="program_id" name="program_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                <option value="">{{ __('None') }}</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected((string) old('program_id', $package?->program_id) === (string) $program->id)>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
            @error('program_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="category_id" class="text-sm font-semibold text-slate-700">{{ __('Category') }}</label>
            <select id="category_id" name="category_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                <option value="">{{ __('None') }}</option>
                @foreach($categories as $category)
                    <optgroup label="{{ $category->name }}">
                        @foreach($category->children as $child)
                            <option value="{{ $child->id }}" @selected((string) old('category_id', $package?->category_id) === (string) $child->id)>
                                {{ $child->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('category_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="levels_count" class="text-sm font-semibold text-slate-700">{{ __('Levels Count') }}</label>
            <input id="levels_count" name="levels_count" type="number" min="1" max="999" value="{{ old('levels_count', $package?->levels_count ?? 1) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('levels_count')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="sessions_count" class="text-sm font-semibold text-slate-700">{{ __('Sessions Count') }}</label>
            <input id="sessions_count" name="sessions_count" type="number" min="0" max="999" value="{{ old('sessions_count', $package?->sessions_count ?? 0) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('sessions_count')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>


        <div>
            <label for="level_price" class="text-sm font-semibold text-slate-700">{{ __('Level Price') }}</label>
            <input id="level_price" name="level_price" type="number" min="0" step="0.01" value="{{ old('level_price', $package?->level_price ?? 0) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('level_price')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ $cancelUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ __('Cancel') }}
    </a>
    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
        {{ $submitLabel }}
    </button>
</div>
