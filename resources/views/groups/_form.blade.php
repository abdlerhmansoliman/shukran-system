@php
    $statusValue = old('status', $group?->status ?? \App\Enums\GroupStatus::Planned->value);
    $selectedDays = collect(old('days_of_week', $group?->days_of_week ?? []))
        ->map(fn ($day) => (string) $day)
        ->all();
    $startDate = old('start_date', $group?->start_date?->format('Y-m-d'));
    $endDate = old('end_date', $group?->end_date?->format('Y-m-d'));
    $startTime = old('start_time', $group?->start_time ? substr((string) $group->start_time, 0, 5) : null);
    $endTime = old('end_time', $group?->end_time ? substr((string) $group->end_time, 0, 5) : null);
    $currentInstructorId = old('instructor_id', $group?->instructor_id);
@endphp

<div 
    x-data="{
        startDate: '{{ $startDate }}',
        endDate: '{{ $endDate }}',
        startTime: '{{ $startTime }}',
        endTime: '{{ $endTime }}',
        days: {{ json_encode($selectedDays) }},
        instructors: {{ json_encode($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])) }},
        selectedInstructor: '{{ $currentInstructorId }}',
        isLoading: false,

        async fetchAvailableInstructors() {
            if (!this.startDate || !this.endDate || !this.startTime || !this.endTime) return;
            
            this.isLoading = true;
            try {
                const params = new URLSearchParams({
                    start_date: this.startDate,
                    end_date: this.endDate,
                    start_time: this.startTime,
                    end_time: this.endTime,
                    group_id: '{{ $group?->id }}'
                });
                this.days.forEach(day => params.append('days_of_week[]', day));

                const response = await fetch(`{{ route('groups.available-instructors') }}?${params.toString()}`);
                this.instructors = await response.json();
            } catch (error) {
                console.error('Failed to fetch instructors:', error);
            } finally {
                this.isLoading = false;
            }
        }
    }"
    x-init="$watch('startDate', () => fetchAvailableInstructors()); $watch('endDate', () => fetchAvailableInstructors()); $watch('startTime', () => fetchAvailableInstructors()); $watch('endTime', () => fetchAvailableInstructors()); $watch('days', () => fetchAvailableInstructors())"
    class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]"
>
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Group Details') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $group?->name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
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
                    <label for="capacity" class="text-sm font-semibold text-slate-700">{{ __('Capacity') }}</label>
                    <input id="capacity" name="capacity" type="number" min="1" max="999" value="{{ old('capacity', $group?->capacity) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('capacity')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Schedule') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="start_date" class="text-sm font-semibold text-slate-700">{{ __('Start Date') }}</label>
                    <input id="start_date" name="start_date" type="date" x-model="startDate" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('start_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="end_date" class="text-sm font-semibold text-slate-700">{{ __('End Date') }}</label>
                    <input id="end_date" name="end_date" type="date" x-model="endDate" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('end_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="start_time" class="text-sm font-semibold text-slate-700">{{ __('Start Time') }}</label>
                    <input id="start_time" name="start_time" type="time" x-model="startTime" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('start_time')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="end_time" class="text-sm font-semibold text-slate-700">{{ __('End Time') }}</label>
                    <input id="end_time" name="end_time" type="time" x-model="endTime" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('end_time')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm font-semibold text-slate-700">{{ __('Days') }}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($weekDays as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                <input
                                    type="checkbox"
                                    name="days_of_week[]"
                                    value="{{ $value }}"
                                    x-model="days"
                                    class="group-day-checkbox"
                                >
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('days_of_week')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    @error('days_of_week.*')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Course Context') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="package_id" class="text-sm font-semibold text-slate-700">{{ __('Product') }}</label>
                    <select id="package_id" name="package_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" @selected((string) old('package_id', $group?->package_id) === (string) $package->id)>{{ $package->name }}</option>
                        @endforeach
                    </select>
                    @error('package_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="level_id" class="text-sm font-semibold text-slate-700">{{ __('Level') }}</label>
                    <select id="level_id" name="level_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected((string) old('level_id', $group?->level_id) === (string) $level->id)>{{ $level->name }}</option>
                        @endforeach
                    </select>
                    @error('level_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="instructor_id" class="text-sm font-semibold text-slate-700">
                        {{ __('Instructor') }}
                        <span x-show="isLoading" class="ml-2 inline-block h-3 w-3 animate-spin rounded-full border-2 border-slate-300 border-t-slate-900"></span>
                    </label>
                    <select id="instructor_id" name="instructor_id" x-model="selectedInstructor" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Not specified') }}</option>
                        <template x-for="instructor in instructors" :key="instructor.id">
                            <option :value="instructor.id" x-text="instructor.name" :selected="selectedInstructor == instructor.id"></option>
                        </template>
                    </select>
                    @error('instructor_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
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
