@php
    $statusValue = old('status', $customer?->status ?? \App\Enums\CustomerStatus::Inactive->value);
    $placementMonth = old('placement_month', $customer?->placement_month?->format('Y-m-d'));
    $selectedPackageId = old('package_id', $customer?->customerPackages?->sortByDesc('created_at')->first()?->package_id);
@endphp

<div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Contact Information') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="first_name" class="text-sm font-semibold text-slate-700">{{ __('First Name') }}</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $customer?->first_name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('first_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="last_name" class="text-sm font-semibold text-slate-700">{{ __('Last Name') }}</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $customer?->last_name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('last_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="text-sm font-semibold text-slate-700">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $customer?->phone) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('phone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $customer?->email) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="text-sm font-semibold text-slate-700">{{ __('Address') }}</label>
                    <input id="address" name="address" type="text" value="{{ old('address', $customer?->address) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('address')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Details') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
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
                    <label for="source" class="text-sm font-semibold text-slate-700">{{ __('Source') }}</label>
                    <select id="source" name="source" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach(['website', 'whatsapp', 'facebook', 'instagram', 'referral', 'sales call'] as $source)
                            <option value="{{ $source }}" @selected(old('source', $customer?->source) === $source)>{{ __(\Illuminate\Support\Str::headline($source)) }}</option>
                        @endforeach
                    </select>
                    @error('source')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="customer_type" class="text-sm font-semibold text-slate-700">{{ __('Customer Type') }}</label>
                    <select id="customer_type" name="customer_type" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="new" @selected(old('customer_type', $customer?->customer_type ?? 'new') === 'new')>{{ __('New') }}</option>
                        <option value="old" @selected(old('customer_type', $customer?->customer_type) === 'old')>{{ __('Old') }}</option>
                    </select>
                    @error('customer_type')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="placement_month" class="text-sm font-semibold text-slate-700">{{ __('Placement Date') }}</label>
                    <input id="placement_month" name="placement_month" type="date" value="{{ $placementMonth }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('placement_month')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="age" class="text-sm font-semibold text-slate-700">{{ __('Age') }}</label>
                    <input id="age" name="age" type="number" min="0" max="120" value="{{ old('age', $customer?->age) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('age')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="gender" class="text-sm font-semibold text-slate-700">{{ __('Gender') }}</label>
                    <select id="gender" name="gender" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        <option value="male" @selected(old('gender', $customer?->gender) === 'male')>{{ __('Male') }}</option>
                        <option value="female" @selected(old('gender', $customer?->gender) === 'female')>{{ __('Female') }}</option>
                    </select>
                    @error('gender')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Classification') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="level_id" class="text-sm font-semibold text-slate-700">{{ __('Level') }}</label>
                    <select id="level_id" name="level_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected((string) old('level_id', $customer?->level_id) === (string) $level->id)>{{ $level->name }}</option>
                        @endforeach
                    </select>
                    @error('level_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="text-sm font-semibold text-slate-700">{{ __('Category') }}</label>
                    <select id="category_id" name="category_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $customer?->category_id) === (string) $category->id)>
                                {{ $category->parent ? $category->parent->name . ' / ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="country_id" class="text-sm font-semibold text-slate-700">{{ __('Country') }}</label>
                    <select id="country_id" name="country_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" @selected((string) old('country_id', $customer?->country_id) === (string) $country->id)>
                                {{ $country->name }}@if($country->code) ({{ $country->code }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Package') }}</p>

            <div class="mt-6">
                <label for="package_id" class="text-sm font-semibold text-slate-700">{{ __('Package') }}</label>
                <select id="package_id" name="package_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    <option value="">{{ __('Not specified') }}</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" @selected((string) $selectedPackageId === (string) $package->id)>
                            {{ $package->name }} - {{ number_format((float) $package->price, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('package_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Placement Context') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="tester_id" class="text-sm font-semibold text-slate-700">{{ __('Tester') }}</label>
                    <select id="tester_id" name="tester_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('tester_id', $customer?->tester_id) === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('tester_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="old_instructor_id" class="text-sm font-semibold text-slate-700">{{ __('Instructor') }}</label>
                    <select id="old_instructor_id" name="old_instructor_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('old_instructor_id', $customer?->old_instructor_id) === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('old_instructor_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Notes') }}</p>
            <textarea id="notes" name="notes" rows="5" class="mt-6 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">{{ old('notes', $customer?->notes) }}</textarea>
            @error('notes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
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
