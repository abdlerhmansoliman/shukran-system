@php
    $placementMonth = old('placement_month', $customer?->placement_month?->format('Y-m-d'));
    $packageAssignmentRows = collect(old('package_assignments', [['package_id' => '', 'levels_count' => 1]]))
        ->map(fn ($assignment) => [
            'package_id' => $assignment['package_id'] ?? '',
            'discount_id' => $assignment['discount_id'] ?? '',
            'levels_count' => $assignment['levels_count'] ?? 1,
        ])
        ->values();

    if ($packageAssignmentRows->isEmpty()) {
        $packageAssignmentRows = collect([['package_id' => '', 'discount_id' => '', 'levels_count' => 1]]);
    }

    $currentSubscriptions = $customer?->customerPackages?->sortByDesc('created_at')->values() ?? collect();
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
                    <label for="second_phone_number" class="text-sm font-semibold text-slate-700">{{ __('Second Phone') }}</label>
                    <input id="second_phone_number" name="second_phone_number" type="text" value="{{ old('second_phone_number', $customer?->second_phone_number) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('second_phone_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
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
                    <label for="wallet_balance" class="text-sm font-semibold text-slate-700">{{ __('Wallet Balance') }}</label>
                    <input id="wallet_balance" name="wallet_balance" type="number" min="0" step="0.01" value="{{ old('wallet_balance', $customer?->wallet_balance ?? '0.00') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('wallet_balance')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
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
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Subscriptions') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Add one or more subscriptions. Each subscription is created from a package template.') }}</p>
                </div>
                @if($customer)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ trans_choice('{0} No active subscriptions|{1} :count active subscription|[2,*] :count active subscriptions', $customer->customerPackages?->where('status', 'active')->count() ?? 0, ['count' => $customer->customerPackages?->where('status', 'active')->count() ?? 0]) }}
                    </span>
                @endif
            </div>

            @if($customer)
                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="grid grid-cols-[minmax(0,1fr)_6rem_7rem_7rem] gap-3 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400 max-lg:hidden">
                        <span>{{ __('Subscription') }}</span>
                        <span>{{ __('Status') }}</span>
                        <span>{{ __('Remaining') }}</span>
                        <span class="text-right">{{ __('Action') }}</span>
                    </div>

                    @forelse($currentSubscriptions as $subscription)
                        @php
                            $hasActiveEnrollment = $subscription->groupEnrollments?->contains(fn ($enrollment) => in_array($enrollment->status, ['pending', 'ready', 'active'], true)) ?? false;
                            $statusClasses = match ($subscription->status) {
                                'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'completed' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                default => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                            };
                        @endphp

                        <div class="grid gap-3 border-t border-slate-200 px-4 py-3 text-sm first:border-t-0 lg:grid-cols-[minmax(0,1fr)_6rem_7rem_7rem] lg:items-center">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $subscription->package?->name ?: __('Unknown package') }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __('Started') }}: {{ $subscription->start_date?->format('M d, Y') ?: __('Not scheduled') }}
                                </p>
                            </div>

                            <div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                    {{ __(\Illuminate\Support\Str::headline($subscription->status)) }}
                                </span>
                            </div>

                            <p class="font-semibold text-slate-900">{{ number_format((float) $subscription->remaining_amount, 2) }}</p>

                            <div class="flex justify-start lg:justify-end">
                                @if($subscription->status === 'active')
                                    <button
                                        type="button"
                                        @disabled($hasActiveEnrollment)
                                        x-on:click="$dispatch('open-modal', 'cancel-subscription-{{ $subscription->id }}')"
                                        class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400 disabled:hover:bg-white"
                                    >
                                        {{ __('Remove') }}
                                    </button>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">{{ __('Closed') }}</span>
                                @endif
                            </div>

                            @if($hasActiveEnrollment)
                                <p class="text-xs text-slate-500 lg:col-span-4">{{ __('This subscription is reserved by a group enrollment.') }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="px-4 py-4 text-sm text-slate-500">{{ __('This customer does not have any subscriptions yet.') }}</p>
                    @endforelse
                </div>
            @endif

            @if($packages->isEmpty())
                <p class="mt-5 rounded-2xl border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500">
                    {{ __('No active packages are available right now.') }}
                </p>
            @else
                <div class="mt-5 space-y-3" data-package-assignment-list>
                    @foreach($packageAssignmentRows as $index => $assignment)
                        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-[minmax(0,1fr)_7rem_auto] sm:items-end" data-package-assignment-row>
                            <div>
                                <label for="package_assignments_{{ $index }}_package_id" class="text-sm font-semibold text-slate-700">{{ __('Package Template') }}</label>
                                <select id="package_assignments_{{ $index }}_package_id" name="package_assignments[{{ $index }}][package_id]" class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                                    <option value="">{{ __('No package') }}</option>
                                    @foreach($packages as $package)
                                        <option value="{{ $package->id }}" @selected((string) $assignment['package_id'] === (string) $package->id)>
                                            {{ $package->name }} - {{ __('Level Price: :price', ['price' => number_format((float) $package->level_price, 2)]) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="package_assignments_{{ $index }}_discount_id" class="text-sm font-semibold text-slate-700">{{ __('Discount') }}</label>
                                <select id="package_assignments_{{ $index }}_discount_id" name="package_assignments[{{ $index }}][discount_id]" class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                                    <option value="">{{ __('No discount') }}</option>
                                    @foreach($discounts as $discount)
                                        <option value="{{ $discount->id }}" @selected((string) ($assignment['discount_id'] ?? '') === (string) $discount->id)>
                                            {{ $discount->name }} ({{ $discount->type === 'percentage' ? $discount->amount . '%' : $discount->amount . ' fixed' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="package_assignments_{{ $index }}_levels_count" class="text-sm font-semibold text-slate-700">{{ __('Levels') }}</label>
                                <input id="package_assignments_{{ $index }}_levels_count" name="package_assignments[{{ $index }}][levels_count]" type="number" min="1" max="50" value="{{ $assignment['levels_count'] }}" class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            </div>

                            <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100" data-package-assignment-remove>
                                {{ __('Remove') }}
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="mt-4 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-package-assignment-add>
                    {{ __('Add another package') }}
                </button>

                @error('package_assignments')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('package_assignments.*.package_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('package_assignments.*.discount_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('package_assignments.*.levels_count')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            @endif
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

@if($customer)
    @push('scripts')
        @foreach($currentSubscriptions as $subscription)
            @if($subscription->status === 'active')
                <form id="remove_subscription_{{ $subscription->id }}" method="POST" action="{{ route('customers.subscriptions.destroy', [$customer, $subscription]) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <x-modal name="cancel-subscription-{{ $subscription->id }}" :show="false" maxWidth="lg" focusable>
                    <form method="POST" action="{{ route('customers.subscriptions.destroy', [$customer, $subscription]) }}" class="p-6">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="cancel_subscription_id" value="{{ $subscription->id }}">

                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('Cancel Subscription') }}</h2>
                            <p class="mt-2 text-sm text-slate-600">{{ __('Remove this subscription from the customer?') }}</p>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Paid Amount') }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $subscription->paid_amount, 2) }}</p>
                        </div>

                        <div class="mt-5">
                            <label for="refund_amount_{{ $subscription->id }}" class="block text-sm font-semibold text-slate-900">{{ __('Refund Amount') }}</label>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ (float) $subscription->paid_amount > 0
                                    ? __('Enter the amount to return to the customer wallet.')
                                    : __('No paid amount is available to return.') }}
                            </p>
                            <input
                                id="refund_amount_{{ $subscription->id }}"
                                name="refund_amount"
                                type="number"
                                min="0"
                                max="{{ number_format((float) $subscription->paid_amount, 2, '.', '') }}"
                                step="0.01"
                                value="{{ (string) old('cancel_subscription_id') === (string) $subscription->id
                                    ? old('refund_amount')
                                    : ((float) $subscription->paid_amount > 0 ? number_format((float) $subscription->paid_amount, 2, '.', '') : '0.00') }}"
                                @disabled((float) $subscription->paid_amount <= 0)
                                class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-50"
                            >
                            <p class="mt-2 text-xs text-slate-500">{{ __('Leave it as 0 to cancel without a refund.') }}</p>
                            @if((string) old('cancel_subscription_id') === (string) $subscription->id)
                                @error('refund_amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            @endif
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                x-on:click="$dispatch('close-modal', 'cancel-subscription-{{ $subscription->id }}')"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                {{ __('Keep Subscription') }}
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700"
                            >
                                {{ __('Cancel Subscription') }}
                            </button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endforeach
    @endpush
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @if(old('cancel_subscription_id'))
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'cancel-subscription-{{ old('cancel_subscription_id') }}'
                    }));
                @endif

                document.querySelectorAll('[data-package-assignment-list]').forEach((list) => {
                    const addButton = list.parentElement.querySelector('[data-package-assignment-add]');
                    const levelsLabel = @js(__('Levels'));

                    const refreshRows = () => {
                        list.querySelectorAll('[data-package-assignment-row]').forEach((row, index) => {
                            row.querySelectorAll('select, input').forEach((field) => {
                                let key = 'package_id';
                                if (field.name.includes('[levels_count]')) key = 'levels_count';
                                if (field.name.includes('[discount_id]')) key = 'discount_id';
                                field.name = `package_assignments[${index}][${key}]`;
                                field.id = `package_assignments_${index}_${key}`;
                            });

                            row.querySelectorAll('label').forEach((label) => {
                                let field = 'package_id';
                                if (label.textContent.trim() === levelsLabel) field = 'levels_count';
                                if (label.textContent.trim() === @js(__('Discount'))) field = 'discount_id';
                                label.setAttribute('for', `package_assignments_${index}_${field}`);
                            });
                        });
                    };

                    const clearRow = (row) => {
                        const packageSelect = row.querySelector('select[name$="[package_id]"]');
                        const discountSelect = row.querySelector('select[name$="[discount_id]"]');
                        const levelsCount = row.querySelector('input[type="number"]');

                        if (packageSelect) packageSelect.value = '';
                        if (discountSelect) discountSelect.value = '';
                        if (levelsCount) levelsCount.value = '1';
                    };

                    list.addEventListener('click', (event) => {
                        const removeButton = event.target.closest('[data-package-assignment-remove]');

                        if (! removeButton) {
                            return;
                        }

                        const rows = list.querySelectorAll('[data-package-assignment-row]');
                        const row = removeButton.closest('[data-package-assignment-row]');

                        if (rows.length === 1) {
                            clearRow(row);
                        } else {
                            row.remove();
                        }

                        refreshRows();
                    });

                    addButton?.addEventListener('click', () => {
                        const firstRow = list.querySelector('[data-package-assignment-row]');

                        if (! firstRow) {
                            return;
                        }

                        const row = firstRow.cloneNode(true);
                        clearRow(row);
                        list.appendChild(row);
                        refreshRows();
                    });

                    refreshRows();
                });
            });
        </script>
    @endpush
@endonce
