@if($category)
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Category Details') }}</p>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="parent_id" class="text-sm font-semibold text-slate-700">{{ __('Parent Category') }}</label>
                <select id="parent_id" name="parent_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    <option value="">{{ __('Root category') }}</option>
                    @foreach($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category?->parent_id) === (string) $parentCategory->id)>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
@else
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Parent Category') }}</p>

        <div class="mt-6">
            <label for="parent_name" class="text-sm font-semibold text-slate-700">{{ __('Parent Category Name') }}</label>
            <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            <p class="mt-2 text-sm text-slate-500">{{ __('Fill this only to create a parent category, or use it as the parent for the child below.') }}</p>
            @error('parent_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Child Category') }}</p>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <label for="child_name" class="text-sm font-semibold text-slate-700">{{ __('Child Category Name') }}</label>
                <input id="child_name" name="child_name" type="text" value="{{ old('child_name') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                @error('child_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="parent_id" class="text-sm font-semibold text-slate-700">{{ __('Existing Parent Category') }}</label>
                <select id="parent_id" name="parent_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    <option value="">{{ __('Choose parent category') }}</option>
                    @foreach($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id') === (string) $parentCategory->id)>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-slate-500">{{ __('A child category must have a parent category.') }}</p>
                @error('parent_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
@endif

<div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ $cancelUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ __('Cancel') }}
    </a>
    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
        {{ $submitLabel }}
    </button>
</div>
